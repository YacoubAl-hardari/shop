<?php

namespace App\Filament\Pages;

use App\Enums\SalePaymentType;
use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Schemas\PaymentDetailsSchema;
use App\Models\MerchantCustomer;
use App\Models\MerchantProduct;
use App\Services\PosSaleService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use JeffersonGoncalves\Filament\QrCodeField\Forms\Components\QrCodeInput;

class PosTerminal extends Page implements HasForms
{
    use HasRoleAccess;
    use InteractsWithForms;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?string $navigationLabel = 'نقطة البيع';

    protected static ?string $title = 'نقطة البيع (POS)';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.pos-terminal';

    public ?array $data = [];

    public string $search = '';

    public string $barcode = '';

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات';
    }

    public function mount(): void
    {
        $this->resetCart();
    }

    public function form($form)
    {
        return $form
            ->schema([
                Repeater::make('items')
                    ->schema([
                        TextInput::make('merchant_product_id')->numeric(),
                        TextInput::make('product_name')->required(),
                        TextInput::make('quantity')->numeric()->required(),
                        TextInput::make('unit_price')->numeric()->required(),
                    ])
                    ->extraAttributes(['class' => 'hidden'])
                    ->hiddenLabel()
                    ->dehydrated(true),

                Section::make('الدفع والتسوية')
                    ->description('بيانات العميل وطريقة السداد')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        Select::make('merchant_customer_id')
                            ->label('العميل')
                            ->options(fn () => MerchantCustomer::query()
                                ->where('team_id', Filament::getTenant()->id)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(fn (Get $get): bool => in_array($get('payment_type'), [
                                SalePaymentType::CREDIT->value,
                                SalePaymentType::PARTIAL->value,
                            ], true))
                            ->helperText(fn (Get $get): ?string => in_array($get('payment_type'), [
                                SalePaymentType::CREDIT->value,
                                SalePaymentType::PARTIAL->value,
                            ], true)
                                ? 'إلزامي للبيع الآجل أو الجزئي لتسجيل الذمة وكشف الحساب'
                                : null)
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (! $state) {
                                    $set('apply_customer_credit', false);

                                    return;
                                }

                                $customer = MerchantCustomer::find($state);
                                $set('apply_customer_credit', $customer?->hasPrepaidBalance() ?? false);
                            })
                            ->createOptionForm([
                                TextInput::make('name')->label('الاسم')->required(),
                                TextInput::make('phone')->label('الهاتف'),
                            ])
                            ->createOptionUsing(fn (array $data) => MerchantCustomer::create($data)->id),

                        Placeholder::make('customer_account_summary')
                            ->label('حساب العميل')
                            ->visible(fn (Get $get): bool => filled($get('merchant_customer_id')))
                            ->content(function (Get $get): string {
                                $customer = $this->getSelectedCustomer($get('merchant_customer_id'));

                                if (! $customer) {
                                    return '—';
                                }

                                $parts = [];

                                if ($customer->hasDebt()) {
                                    $parts[] = 'مديونية: '.number_format($customer->debtBalance(), 2).' ر.س';
                                }

                                if ($customer->hasPrepaidBalance()) {
                                    $parts[] = 'رصيد فائض: '.number_format($customer->prepaidBalance(), 2).' ر.س';
                                }

                                return $parts === [] ? 'لا توجد مديونية أو رصيد فائض' : implode(' | ', $parts);
                            })
                            ->columnSpanFull(),

                        Toggle::make('apply_customer_credit')
                            ->label('خصم الرصيد الفائض من الفاتورة')
                            ->helperText(fn (Get $get): ?string => $this->getSelectedCustomer($get('merchant_customer_id'))?->hasPrepaidBalance()
                                ? 'يُخصم تلقائياً من المبلغ المستحق على العميل'
                                : null)
                            ->default(false)
                            ->live()
                            ->visible(fn (Get $get): bool => $this->getSelectedCustomer($get('merchant_customer_id'))?->hasPrepaidBalance() ?? false)
                            ->columnSpanFull(),

                        Select::make('payment_type')
                            ->label('نوع الدفع')
                            ->options(SalePaymentType::options())
                            ->required()
                            ->live()
                            ->default(SalePaymentType::CASH->value),

                        TextInput::make('paid_amount')
                            ->label('المبلغ المدفوع')
                            ->numeric()
                            ->prefix('ر.س')
                            ->live()
                            ->required(fn (Get $get): bool => $get('payment_type') === SalePaymentType::PARTIAL->value)
                            ->minValue(0.01)
                            ->maxValue(fn (Get $get): ?float => $this->maxPartialPaidAmount([
                                'items' => $get('items') ?? [],
                                'merchant_customer_id' => $get('merchant_customer_id'),
                                'apply_customer_credit' => $get('apply_customer_credit'),
                            ]))
                            ->validationMessages([
                                'max' => 'المبلغ المدفوع للبيع الجزئي يجب أن يكون أقل من صافي قيمة الفاتورة (:max).',
                            ])
                            ->visible(fn (Get $get): bool => $get('payment_type') === SalePaymentType::PARTIAL->value),

                        Section::make('تفاصيل الدفع')
                            ->visible(fn (Get $get): bool => $get('payment_type') !== SalePaymentType::CREDIT->value
                                && $this->netSaleTotal([
                                    'items' => $get('items') ?? [],
                                    'merchant_customer_id' => $get('merchant_customer_id'),
                                    'apply_customer_credit' => $get('apply_customer_credit'),
                                ]) > 0)
                            ->schema([
                                PaymentDetailsSchema::methodSelect(),
                                PaymentDetailsSchema::accountSelect(),
                                PaymentDetailsSchema::accountPreview(),
                                PaymentDetailsSchema::referenceInput(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),

                        Placeholder::make('cart_subtotal')
                            ->label('إجمالي الفاتورة')
                            ->columnSpanFull()
                            ->content(fn (Get $get): string => number_format($this->calculateTotal($get('items') ?? []), 2).' ر.س')
                            ->extraAttributes(['class' => 'text-2xl font-bold text-primary-600']),

                        Placeholder::make('customer_credit_deduction')
                            ->label('خصم الرصيد الفائض')
                            ->visible(fn (Get $get): bool => $this->calculateCreditApplied([
                                'items' => $get('items') ?? [],
                                'merchant_customer_id' => $get('merchant_customer_id'),
                                'apply_customer_credit' => $get('apply_customer_credit'),
                            ]) > 0)
                            ->content(fn (Get $get): string => '- '.number_format($this->calculateCreditApplied([
                                'items' => $get('items') ?? [],
                                'merchant_customer_id' => $get('merchant_customer_id'),
                                'apply_customer_credit' => $get('apply_customer_credit'),
                            ]), 2).' ر.س')
                            ->columnSpanFull(),

                        Placeholder::make('amount_due')
                            ->label('المبلغ المستحق')
                            ->columnSpanFull()
                            ->content(fn (Get $get): string => number_format($this->netSaleTotal([
                                'items' => $get('items') ?? [],
                                'merchant_customer_id' => $get('merchant_customer_id'),
                                'apply_customer_credit' => $get('apply_customer_credit'),
                            ]), 2).' ر.س')
                            ->extraAttributes(['class' => 'text-xl font-semibold']),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(['default' => 1, 'sm' => 2])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function canCompleteSale(): bool
    {
        return $this->getCompleteSaleBlockReason() === null;
    }

    public function getCompleteSaleBlockReason(): ?string
    {
        return $this->validateSaleData($this->data ?? []);
    }

    public function completeSale(): void
    {
        if (! $this->canCompleteSale()) {
            Notification::make()
                ->title('خطأ في التحقق')
                ->body($this->getCompleteSaleBlockReason() ?? 'يرجى تعبئة جميع الحقول المطلوبة')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $team = Filament::getTenant();
        $customer = ! empty($data['merchant_customer_id'])
            ? MerchantCustomer::find($data['merchant_customer_id'])
            : null;

        $items = $this->validItems($data['items'] ?? []);
        $paymentType = SalePaymentType::from($data['payment_type']);

        try {
            app(PosSaleService::class)->createSale(
                $team,
                $items,
                $paymentType,
                (float) ($data['paid_amount'] ?? 0),
                $customer,
                $data['payment_method'] ?? 'cash',
                $data['notes'] ?? null,
                $data['merchant_payment_account_id'] ?? null,
                $data['payment_reference'] ?? null,
                $this->calculateCreditApplied($data),
            );

            Notification::make()
                ->title('تم إتمام البيع بنجاح')
                ->success()
                ->send();

            $this->resetCart();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('خطأ في البيع')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function resetCart(): void
    {
        $this->form->fill([
            'payment_type' => SalePaymentType::CASH->value,
            'payment_method' => 'cash',
            'apply_customer_credit' => false,
            'quick_barcode' => null,
            'items' => [],
        ]);
    }

    public function addProduct(int $productId): void
    {
        $product = MerchantProduct::find($productId);

        if (! $product) {
            Notification::make()
                ->title('خطأ')
                ->body('المنتج غير موجود')
                ->danger()
                ->send();

            return;
        }

        $this->appendProductToCart($product);
    }

    public function appendProductToCart(MerchantProduct $product): void
    {
        $items = $this->data['items'] ?? [];
        $found = false;

        foreach ($items as $index => $item) {
            if (($item['merchant_product_id'] ?? null) == $product->id) {
                // Check stock
                $newQty = ($item['quantity'] ?? 1) + 1;
                if ($newQty > (float) $product->stock_quantity) {
                    Notification::make()
                        ->title('تجاوز الحد المتاح')
                        ->body("الكمية المطلوبة للمنتج ({$product->name}) غير متوفرة في المخزن")
                        ->warning()
                        ->send();

                    return;
                }
                $items[$index]['quantity'] = $newQty;
                $found = true;
                break;
            }
        }

        if (! $found) {
            // Check stock for 1 unit
            if (1 > (float) $product->stock_quantity) {
                Notification::make()
                    ->title('نفذت الكمية')
                    ->body("المنتج ({$product->name}) غير متوفر في المخزن")
                    ->warning()
                    ->send();

                return;
            }

            $items[] = [
                'merchant_product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 1,
                'unit_price' => (float) $product->price,
            ];
        }

        $this->data['items'] = array_values($items);

        Notification::make()
            ->title('تم إضافة المنتج')
            ->body($product->name)
            ->success()
            ->send();
    }

    public function incrementQuantity(int $index): void
    {
        if (! isset($this->data['items'][$index])) {
            return;
        }

        $item = $this->data['items'][$index];
        $productId = $item['merchant_product_id'] ?? null;

        if ($productId) {
            $product = MerchantProduct::find($productId);
            if ($product) {
                $newQty = ($item['quantity'] ?? 1) + 1;
                if ($newQty > (float) $product->stock_quantity) {
                    Notification::make()
                        ->title('تجاوز الحد المتاح')
                        ->body("الكمية المطلوبة للمنتج ({$product->name}) غير متوفرة في المخزن")
                        ->warning()
                        ->send();

                    return;
                }
            }
        }

        $this->data['items'][$index]['quantity'] = ($item['quantity'] ?? 1) + 1;
    }

    public function decrementQuantity(int $index): void
    {
        if (! isset($this->data['items'][$index])) {
            return;
        }

        $item = $this->data['items'][$index];
        $newQty = ($item['quantity'] ?? 1) - 1;

        if ($newQty <= 0) {
            $this->removeItem($index);

            return;
        }

        $this->data['items'][$index]['quantity'] = $newQty;
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->data['items'][$index])) {
            return;
        }

        unset($this->data['items'][$index]);
        $this->data['items'] = array_values($this->data['items']);
    }

    public function clearCart(): void
    {
        $this->data['items'] = [];

        Notification::make()
            ->title('تم إفراغ السلة')
            ->success()
            ->send();
    }

    public function scanBarcode(): void
    {
        $code = $this->normalizeBarcode($this->barcode);
        $this->barcode = ''; // Clear immediately

        if ($code === null) {
            $this->dispatch('focus-barcode');

            return;
        }

        $product = $this->findProductByCode($code);

        if ($product) {
            $this->appendProductToCart($product);
        } else {
            Notification::make()
                ->title('منتج غير موجود')
                ->body('لم يتم العثور على منتج بهذا الباركود أو الرمز: '.$code)
                ->danger()
                ->send();
        }

        $this->dispatch('focus-barcode');
    }

    public function getProducts()
    {
        $tenant = Filament::getTenant();
        if (! $tenant) {
            return collect();
        }

        $query = MerchantProduct::query()
            ->where('team_id', $tenant->id)
            ->where('is_active', true);

        if (filled($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                    ->orWhere('barcode', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('name')->get();
    }

    protected function normalizeBarcode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $code = trim($code);
        $code = preg_replace('/[\r\n\t]+/', '', $code) ?? '';

        return $code === '' ? null : $code;
    }

    protected function findProductByCode(string $code): ?MerchantProduct
    {
        $code = $this->normalizeBarcode($code) ?? $code;
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return null;
        }

        return MerchantProduct::query()
            ->where('team_id', $tenant->id)
            ->where('is_active', true)
            ->where(function ($query) use ($code): void {
                $query->where('barcode', $code)
                    ->orWhere('sku', $code);
            })
            ->first();
    }

    protected function calculateTotal(array $items): float
    {
        return (float) collect($items)->sum(
            fn (array $item): float => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0),
        );
    }

    protected function getSelectedCustomer(mixed $customerId): ?MerchantCustomer
    {
        if (empty($customerId)) {
            return null;
        }

        return MerchantCustomer::find($customerId);
    }

    protected function calculateCreditApplied(array $data): float
    {
        if (empty($data['merchant_customer_id']) || ! ($data['apply_customer_credit'] ?? false)) {
            return 0;
        }

        $customer = $this->getSelectedCustomer($data['merchant_customer_id']);

        if (! $customer) {
            return 0;
        }

        $total = $this->calculateTotal($data['items'] ?? []);

        return min($customer->prepaidBalance(), $total);
    }

    protected function netSaleTotal(array $data): float
    {
        $total = $this->calculateTotal($data['items'] ?? []);

        return max(0, $total - $this->calculateCreditApplied($data));
    }

    protected function maxPartialPaidAmount(array $data): ?float
    {
        $net = $this->netSaleTotal($data);

        if ($net <= 0.01) {
            return null;
        }

        return round($net - 0.01, 2);
    }

    protected function validItems(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item): bool => filled($item['product_name'] ?? null)
                && (float) ($item['quantity'] ?? 0) > 0
                && (float) ($item['unit_price'] ?? 0) > 0)
            ->values()
            ->all();
    }

    protected function validateSaleData(array $data): ?string
    {
        $items = $this->validItems($data['items'] ?? []);

        if ($items === []) {
            return 'أضف صنفاً واحداً على الأقل مع الاسم والكمية والسعر';
        }

        // التحقق من توافر المخزون
        foreach ($items as $item) {
            if (! empty($item['merchant_product_id'])) {
                $product = MerchantProduct::find($item['merchant_product_id']);
                if ($product) {
                    $requestedQty = (float) ($item['quantity'] ?? 0);
                    $availableQty = (float) $product->stock_quantity;
                    if ($requestedQty > $availableQty) {
                        return "الكمية المطلوبة للمنتج ({$product->name}) هي " . number_format($requestedQty, 2) . "، ولكن المتاح في المخزن هو " . number_format($availableQty, 2) . " فقط.";
                    }
                }
            }
        }

        $paymentType = $data['payment_type'] ?? SalePaymentType::CASH->value;

        if (in_array($paymentType, [SalePaymentType::CREDIT->value, SalePaymentType::PARTIAL->value], true)
            && empty($data['merchant_customer_id'])) {
            return 'يجب اختيار عميل مسجّل للبيع الآجل أو الجزئي';
        }

        $netDue = $this->netSaleTotal($data);

        if ($paymentType === SalePaymentType::PARTIAL->value) {
            if ($netDue <= 0) {
                return 'الفاتورة مغطاة بالرصيد الفائض — استخدم الدفع النقدي';
            }

            $paid = (float) ($data['paid_amount'] ?? 0);

            if ($paid <= 0) {
                return 'أدخل المبلغ المدفوع للبيع الجزئي';
            }

            if ($paid >= $netDue) {
                return 'المبلغ المدفوع يجب أن يكون أقل من المبلغ المستحق بعد خصم الرصيد الفائض';
            }
        }

        $paymentMethod = $data['payment_method'] ?? 'cash';

        if ($paymentType !== SalePaymentType::CREDIT->value
            && $netDue > 0
            && PaymentDetailsSchema::requiresAccount($paymentMethod)) {
            if (empty($data['merchant_payment_account_id'])) {
                return $paymentMethod === 'bank_transfer'
                    ? 'يجب اختيار البنك'
                    : 'يجب اختيار البطاقة أو المحفظة';
            }

            if (empty($data['payment_reference'])) {
                return $paymentMethod === 'bank_transfer'
                    ? 'رقم الحوالة مطلوب'
                    : 'رقم العملية مطلوب';
            }
        }

        return null;
    }
}
