<?php

namespace App\Services;

use App\Enums\CustomerFinancialTransferPurpose;
use App\Enums\CustomerFinancialTransferStatus;
use App\Enums\MerchantPaymentAccountType;
use App\Filament\Resources\MerchantCustomerFinancialTransfers\MerchantCustomerFinancialTransferResource;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerFinancialTransfer;
use App\Models\MerchantCustomerPayment;
use App\Models\MerchantCustomerStatementShare;
use App\Models\MerchantPaymentAccount;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerFinancialTransferService
{
    public function __construct(
        protected PosSaleService $posSaleService,
    ) {}

    public function submit(
        MerchantCustomerStatementShare $share,
        User $customer,
        float $amount,
        string $paymentMethod = 'cash',
        ?int $merchantPaymentAccountId = null,
        ?string $referenceNumber = null,
        ?string $notes = null,
        CustomerFinancialTransferPurpose $purpose = CustomerFinancialTransferPurpose::SETTLEMENT,
    ): MerchantCustomerFinancialTransfer {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('المبلغ يجب أن يكون أكبر من صفر');
        }

        if (! $share->is_active) {
            throw new \InvalidArgumentException('مشاركة كشف الحساب غير نشطة');
        }

        $merchantCustomer = MerchantCustomer::acrossTeams()
            ->findOrFail($share->merchant_customer_id);

        if ((int) $merchantCustomer->user_id !== (int) $customer->id) {
            throw new \InvalidArgumentException('غير مصرح لك بتقديم سداد على هذا الكشف');
        }

        $this->validatePaymentAccount($share->team_id, $paymentMethod, $merchantPaymentAccountId);

        $hasPending = MerchantCustomerFinancialTransfer::acrossTeams()
            ->where('merchant_customer_id', $merchantCustomer->id)
            ->where('team_id', $share->team_id)
            ->pending()
            ->exists();

        if ($hasPending) {
            throw new \InvalidArgumentException('لديك عملية سداد بانتظار التأكيد. انتظر رد التاجر أو ألغِ العملية الحالية.');
        }

        return DB::transaction(function () use (
            $share,
            $customer,
            $merchantCustomer,
            $amount,
            $paymentMethod,
            $merchantPaymentAccountId,
            $referenceNumber,
            $notes,
            $purpose,
        ) {
            $merchantTeamId = (int) $share->team_id;

            $transfer = MerchantCustomerFinancialTransfer::create([
                'team_id' => $merchantTeamId,
                'merchant_customer_id' => $merchantCustomer->id,
                'statement_share_id' => $share->id,
                'submitted_by' => $customer->id,
                'merchant_payment_account_id' => $merchantPaymentAccountId,
                'payment_method' => $paymentMethod,
                'purpose' => $purpose,
                'amount' => $amount,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'status' => CustomerFinancialTransferStatus::PENDING,
            ]);

            if ((int) $transfer->team_id !== $merchantTeamId) {
                $transfer->update(['team_id' => $merchantTeamId]);
            }

            $this->notifyMerchantsOfSubmission($transfer->load('statementShare'), $merchantCustomer);

            return $transfer;
        });
    }

    public function approve(
        MerchantCustomerFinancialTransfer $transfer,
        User $reviewer,
    ): MerchantCustomerPayment {
        return $this->withLockedPendingTransfer($transfer, function (MerchantCustomerFinancialTransfer $locked) use ($reviewer): MerchantCustomerPayment {
            $team = Team::findOrFail($locked->team_id);
            $customer = MerchantCustomer::acrossTeams()
                ->findOrFail($locked->merchant_customer_id);

            $prepaidOnly = $locked->purpose === CustomerFinancialTransferPurpose::PREPAID;

            $payment = $this->posSaleService->recordCustomerPayment(
                $team,
                $customer,
                (float) $locked->amount,
                $locked->payment_method,
                $locked->merchant_payment_account_id,
                $locked->reference_number,
                $locked->notes,
                $reviewer->id,
                $prepaidOnly,
            );

            $locked->update([
                'status' => CustomerFinancialTransferStatus::APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'merchant_customer_payment_id' => $payment->id,
            ]);

            $this->notifyCustomerOfReview($locked->fresh(['team']), approved: true);

            return $payment;
        }, forMerchant: true);
    }

    public function reject(
        MerchantCustomerFinancialTransfer $transfer,
        User $reviewer,
        string $reason,
    ): void {
        if (blank(trim($reason))) {
            throw new \InvalidArgumentException('يجب إدخال سبب الرفض');
        }

        $this->withLockedPendingTransfer($transfer, function (MerchantCustomerFinancialTransfer $locked) use ($reviewer, $reason): void {
            $locked->update([
                'status' => CustomerFinancialTransferStatus::REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => trim($reason),
            ]);

            $this->notifyCustomerOfReview($locked->fresh(['team']), approved: false);
        }, forMerchant: true);
    }

    public function cancel(
        MerchantCustomerFinancialTransfer $transfer,
        User $customer,
    ): void {
        if ((int) $transfer->submitted_by !== (int) $customer->id) {
            throw new \InvalidArgumentException('غير مصرح لك بإلغاء هذه العملية');
        }

        $this->withLockedPendingTransfer($transfer, function (MerchantCustomerFinancialTransfer $locked) use ($customer): void {
            $locked->update([
                'status' => CustomerFinancialTransferStatus::CANCELLED,
                'reviewed_at' => now(),
            ]);

            $merchantCustomer = MerchantCustomer::acrossTeams()
                ->find($locked->merchant_customer_id);

            if ($merchantCustomer) {
                $this->notifyMerchantsOfCancellation($locked->fresh(['statementShare']), $merchantCustomer, $customer);
            }
        }, forMerchant: false);
    }

    /**
     * @template T
     *
     * @param  callable(MerchantCustomerFinancialTransfer): T  $callback
     * @return T
     */
    protected function withLockedPendingTransfer(
        MerchantCustomerFinancialTransfer $transfer,
        callable $callback,
        bool $forMerchant,
    ): mixed {
        return DB::transaction(function () use ($transfer, $callback, $forMerchant) {
            $locked = MerchantCustomerFinancialTransfer::acrossTeams()
                ->whereKey($transfer->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->isPending()) {
                throw new \InvalidArgumentException(
                    $this->pendingConflictMessage($locked, $forMerchant),
                );
            }

            return $callback($locked);
        });
    }

    protected function pendingConflictMessage(
        ?MerchantCustomerFinancialTransfer $transfer,
        bool $forMerchant,
    ): string {
        if (! $transfer) {
            return 'الطلب غير موجود';
        }

        return match ($transfer->status) {
            CustomerFinancialTransferStatus::APPROVED => $forMerchant
                ? 'تم تأكيد الطلب مسبقاً'
                : 'أكّد التاجر الطلب قبل الإلغاء ولا يمكن إلغاؤه',
            CustomerFinancialTransferStatus::CANCELLED => $forMerchant
                ? 'ألغى العميل الطلب قبل التأكيد'
                : 'تم إلغاء الطلب مسبقاً',
            CustomerFinancialTransferStatus::REJECTED => 'تم رفض الطلب مسبقاً',
            default => 'لم يعد الطلب معلّقاً',
        };
    }

    protected function validatePaymentAccount(
        int $teamId,
        string $paymentMethod,
        ?int $merchantPaymentAccountId,
    ): void {
        $requiresAccount = in_array($paymentMethod, ['card', 'bank_transfer'], true);

        if ($requiresAccount && ! $merchantPaymentAccountId) {
            throw new \InvalidArgumentException('يجب اختيار حساب الدفع');
        }

        if (! $merchantPaymentAccountId) {
            return;
        }

        $expectedType = $paymentMethod === 'bank_transfer'
            ? MerchantPaymentAccountType::BANK
            : MerchantPaymentAccountType::CARD;

        $account = MerchantPaymentAccount::withoutGlobalScopes()
            ->where('team_id', $teamId)
            ->where('id', $merchantPaymentAccountId)
            ->where('is_active', true)
            ->first();

        if (! $account || $account->type !== $expectedType) {
            throw new \InvalidArgumentException('حساب الدفع المحدد غير صالح');
        }
    }

    protected function notifyMerchantsOfSubmission(
        MerchantCustomerFinancialTransfer $transfer,
        MerchantCustomer $customer,
    ): void {
        $purposeLabel = $transfer->purpose->getLabel();
        $amount = number_format((float) $transfer->amount, 2);

        $this->notifyMerchantRecipients(
            $transfer,
            title: 'طلب سداد من عميل',
            body: "قدّم العميل «{$customer->name}» {$purposeLabel} بمبلغ {$amount} ر.س — بانتظار التأكيد.",
            icon: 'heroicon-o-banknotes',
            color: 'warning',
            withReviewAction: true,
        );
    }

    protected function notifyMerchantsOfCancellation(
        MerchantCustomerFinancialTransfer $transfer,
        MerchantCustomer $customer,
        User $cancelledBy,
    ): void {
        $amount = number_format((float) $transfer->amount, 2);
        $customerLabel = $cancelledBy->name ?: $customer->name;

        $this->notifyMerchantRecipients(
            $transfer,
            title: 'إلغاء طلب سداد',
            body: "ألغى العميل «{$customerLabel}» طلب السداد بمبلغ {$amount} ر.س.",
            icon: 'heroicon-o-x-circle',
            color: 'gray',
            withReviewAction: false,
        );
    }

    protected function notifyMerchantRecipients(
        MerchantCustomerFinancialTransfer $transfer,
        string $title,
        string $body,
        string $icon,
        string $color,
        bool $withReviewAction,
    ): void {
        $team = Team::find($transfer->team_id);
        $recipients = $this->merchantRecipients($transfer, $team);
        $merchantTenant = $team ?? Team::find($transfer->team_id);

        foreach ($recipients as $merchantUser) {
            $notification = $this->applyNotificationStatus(
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->icon($icon),
                $color,
            );

            if ($withReviewAction && $merchantTenant) {
                $notification->actions([
                    Action::make('review')
                        ->label('مراجعة الطلب')
                        ->button()
                        ->markAsRead()
                        ->url(MerchantCustomerFinancialTransferResource::getUrl(
                            'view',
                            ['record' => $transfer->id],
                            tenant: $merchantTenant,
                        )),
                ]);
            }

            $notification->sendToDatabase($merchantUser, isEventDispatched: true);
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function merchantRecipients(
        MerchantCustomerFinancialTransfer $transfer,
        ?Team $team,
    ): \Illuminate\Support\Collection {
        $recipients = $team?->members
            ->filter(fn (User $user) => $user->isMerchant() || $user->isAdmin()) ?? collect();

        if ($recipients->isEmpty() && $transfer->statementShare?->shared_by) {
            $sharedBy = User::find($transfer->statementShare->shared_by);

            if ($sharedBy) {
                return collect([$sharedBy]);
            }
        }

        return $recipients;
    }

    protected function applyNotificationStatus(Notification $notification, string $color): Notification
    {
        return match ($color) {
            'success' => $notification->success(),
            'danger' => $notification->danger(),
            'gray' => $notification->warning(),
            default => $notification->warning(),
        };
    }

    protected function notifyCustomerOfReview(
        MerchantCustomerFinancialTransfer $transfer,
        bool $approved,
    ): void {
        $customerUser = User::find($transfer->submitted_by);

        if (! $customerUser) {
            return;
        }

        $teamName = $transfer->team?->name ?? 'التاجر';
        $amount = number_format((float) $transfer->amount, 2);

        if ($approved) {
            Notification::make()
                ->title('تم تأكيد استلام المبلغ')
                ->body("أكّد التاجر «{$teamName}» استلام مبلغ {$amount} ر.س وتم تسجيله في كشف حسابك.")
                ->icon('heroicon-o-check-circle')
                ->success()
                ->sendToDatabase($customerUser, isEventDispatched: true);

            return;
        }

        $body = "رفض التاجر «{$teamName}» عملية السداد بمبلغ {$amount} ر.س.";

        if ($transfer->rejection_reason) {
            $body .= ' السبب: '.$transfer->rejection_reason;
        }

        Notification::make()
            ->title('تم رفض عملية السداد')
            ->body($body)
            ->icon('heroicon-o-x-circle')
            ->danger()
            ->sendToDatabase($customerUser, isEventDispatched: true);
    }
}
