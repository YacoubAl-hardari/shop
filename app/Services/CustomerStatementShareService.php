<?php

namespace App\Services;

use App\Filament\Pages\ViewSharedCustomerStatement;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerStatementShare;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CustomerStatementShareService
{
    public function share(MerchantCustomer $customer, User $merchant): MerchantCustomerStatementShare
    {
        $customer = MerchantCustomer::acrossTeams()
            ->findOrFail($customer->id);

        if (! $customer->user_id) {
            throw new \InvalidArgumentException('يجب ربط العميل بحساب مستخدم مسجّل في النظام قبل مشاركة كشف الحساب');
        }

        $customerUser = User::find($customer->user_id);

        if (! $customerUser || ! $customerUser->isUser()) {
            throw new \InvalidArgumentException('العميل غير مرتبط بحساب مستخدم صالح');
        }

        return DB::transaction(function () use ($customer, $merchant, $customerUser) {
            $this->closeActiveShare($customer, $merchant, notify: false);

            $share = MerchantCustomerStatementShare::create([
                'team_id' => $customer->team_id,
                'merchant_customer_id' => $customer->id,
                'user_id' => $customer->user_id,
                'shared_by' => $merchant->id,
                'is_active' => true,
                'shared_at' => now(),
            ]);

            $merchantName = $customer->team?->name ?? 'تاجر';
            $customerTenant = $customerUser->teams()->first();

            Notification::make()
                ->title('تمت مشاركة كشف حسابك')
                ->body("شارك التاجر «{$merchantName}» كشف حسابك معك.")
                ->icon('heroicon-o-document-text')
                ->success()
                ->actions([
                    Action::make('view')
                        ->label('عرض كشف الحساب')
                        ->button()
                        ->markAsRead()
                        ->url(ViewSharedCustomerStatement::statementUrl($share->uuid, $customerTenant)),
                ])
                ->sendToDatabase($customerUser, isEventDispatched: true);

            return $share;
        });
    }

    public function closeActiveShare(MerchantCustomer $customer, User $merchant, bool $notify = true): void
    {
        $share = MerchantCustomerStatementShare::query()
            ->where('merchant_customer_id', $customer->id)
            ->where('is_active', true)
            ->first();

        if (! $share) {
            return;
        }

        $share->update([
            'is_active' => false,
            'closed_at' => now(),
            'closed_by' => $merchant->id,
        ]);

        if ($notify && $share->user) {
            $merchantName = $customer->team?->name ?? 'تاجر';

            Notification::make()
                ->title('تم إغلاق مشاركة كشف الحساب')
                ->body("أغلق التاجر «{$merchantName}» مشاركة كشف حسابك. لم يعد بإمكانك الاطلاع عليه.")
                ->icon('heroicon-o-x-circle')
                ->warning()
                ->actions([
                    Action::make('dismiss')
                        ->label('حسناً')
                        ->button()
                        ->markAsRead(),
                ])
                ->sendToDatabase($share->user, isEventDispatched: true);
        }
    }

    public function userCanViewShare(MerchantCustomerStatementShare $share, User $user): bool
    {
        if (! $share->is_active) {
            return false;
        }

        if ((int) $share->user_id === (int) $user->id) {
            return true;
        }

        $customer = MerchantCustomer::acrossTeams()
            ->find($share->merchant_customer_id);

        return $customer && (int) $customer->user_id === (int) $user->id;
    }

    public function findViewableShareByUuid(string $uuid, User $user): ?MerchantCustomerStatementShare
    {
        $share = MerchantCustomerStatementShare::query()
            ->where('uuid', $uuid)
            ->first();

        if (! $share) {
            return null;
        }

        $this->reconcileShareRecipient($share);

        if (! $this->userCanViewShare($share->fresh(), $user)) {
            return null;
        }

        return $share->fresh(['team']);
    }

    public function activeSharesQueryForUser(User $user): Builder
    {
        $linkedCustomerIds = MerchantCustomer::acrossTeams()
            ->where('user_id', $user->id)
            ->pluck('id');

        return MerchantCustomerStatementShare::query()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($user, $linkedCustomerIds): void {
                $query->where('user_id', $user->id);

                if ($linkedCustomerIds->isNotEmpty()) {
                    $query->orWhereIn('merchant_customer_id', $linkedCustomerIds);
                }
            });
    }

    public function reconcileShareRecipient(MerchantCustomerStatementShare $share): void
    {
        if (! $share->is_active) {
            return;
        }

        $customer = MerchantCustomer::acrossTeams()
            ->find($share->merchant_customer_id);

        if (! $customer?->user_id) {
            return;
        }

        if ((int) $share->user_id !== (int) $customer->user_id) {
            $share->update(['user_id' => $customer->user_id]);
        }
    }

    public function reconcileActiveSharesForUser(User $user): void
    {
        $this->activeSharesQueryForUser($user)
            ->get()
            ->each(fn (MerchantCustomerStatementShare $share) => $this->reconcileShareRecipient($share));
    }

    public function resolveSharedCustomer(MerchantCustomerStatementShare $share): MerchantCustomer
    {
        return MerchantCustomer::acrossTeams()
            ->findOrFail($share->merchant_customer_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, MerchantCustomerStatementShare>
     */
    public function activeSharesForUser(User $user)
    {
        $this->reconcileActiveSharesForUser($user);

        return $this->activeSharesQueryForUser($user)
            ->with([
                'team',
                'merchantCustomer' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->latest('shared_at')
            ->get();
    }

    public function userHasActiveShares(User $user): bool
    {
        $this->reconcileActiveSharesForUser($user);

        return $this->activeSharesQueryForUser($user)->exists();
    }

    public function handleLinkedUserChange(
        MerchantCustomer $customer,
        ?int $previousUserId,
        User $merchant,
    ): void {
        $newUserId = $customer->user_id;

        if ($this->userIdsMatch($previousUserId, $newUserId)) {
            return;
        }

        $merchantName = $customer->team?->name ?? 'تاجر';
        $customerName = $customer->name;

        if ($previousUserId) {
            $hadActiveShare = MerchantCustomerStatementShare::query()
                ->where('merchant_customer_id', $customer->id)
                ->where('is_active', true)
                ->exists();

            if ($hadActiveShare) {
                $this->closeActiveShare($customer, $merchant, notify: true);
            }

            if (! $hadActiveShare) {
                $previousUser = User::find($previousUserId);

                if ($previousUser) {
                    Notification::make()
                        ->title('تم إلغاء ربط حسابك')
                        ->body("ألغى التاجر «{$merchantName}» ربط حسابك بملف العميل «{$customerName}».")
                        ->icon('heroicon-o-link-slash')
                        ->warning()
                        ->actions([
                            Action::make('dismiss')
                                ->label('حسناً')
                                ->button()
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($previousUser, isEventDispatched: true);
                }
            }
        }

        if ($newUserId) {
            $newUser = User::find($newUserId);

            if ($newUser && $newUser->isUser()) {
                Notification::make()
                    ->title('تم ربط حسابك كعميل')
                    ->body("ربط التاجر «{$merchantName}» حسابك بملف العميل «{$customerName}». يمكنه مشاركة كشف حسابك عند الحاجة.")
                    ->icon('heroicon-o-user-plus')
                    ->success()
                    ->actions([
                        Action::make('dismiss')
                            ->label('حسناً')
                            ->button()
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($newUser, isEventDispatched: true);
            }
        }
    }

    private function userIdsMatch(?int $first, ?int $second): bool
    {
        if ($first === null && $second === null) {
            return true;
        }

        if ($first === null || $second === null) {
            return false;
        }

        return (int) $first === (int) $second;
    }
}
