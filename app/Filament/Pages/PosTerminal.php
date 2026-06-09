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
                Grid::make()
                    ->columns(['default' => 1, 'xl' => 3])
                    ->schema([
                        Grid::make()
                            ->columnSpan(['default' => 1, 'xl' => 2])
                            ->schema([
                                Section::make('سلة المبيعات')
                                    ->description('الأصناف المضافة للفاتورة الحالية')
                                    ->icon(Heroicon::OutlinedShoppingCart)
                                    ->schema([
                                        Repeater::make('items')
                                            ->label('الأصناف')
                                            ->live()
                                            ->defaultItems(1)
                                            ->addActionLabel('إضافة صنف')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): string => $state['product_name'] ?? 'صنف جديد')
                                            ->schema([
                                                Toggle::make('use_barcode_search')
                                                    ->label('البحث بالباركود')
                                                    ->default(false)
                                                    ->live()
                                                    ->columnSpanFull(),

                                                Select::make('merchant_product_id')
                                                    ->label('المنتج')
                                                    ->options(fn () => MerchantProduct::query()
                                                        ->where('is_active', true)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->visible(fn (Get $get): bool => ! $get('use_barcode_search'))
                                                    ->afterStateUpdated(function ($state, Set $set): void {
                                                        if (! $state) {
                                                            return;
                                                        }

                                                        $product = MerchantProduct::find($state);

                                                        if ($product) {
                                                            $set('product_name', $product->name);
                                                            $set('unit_price', (float) $product->price);
                                                        }
                                                    }),

                                                QrCodeInput::make('barcode_search')
                                                    ->label('مسح الباركود')
                                                    ->live(debounce: 400)
                                                    ->icon('heroicon-o-qr-code')
                                                    ->visible(fn (Get $get): bool => $get('use_barcode_search') && ! $get('merchant_product_id'))
                                                    ->afterStateUpdated(fn (?string $state, Set $set) => $this->handleRepeaterBarcodeScan($state, $set)),

                                                TextInput::make('product_name')
                                                    ->label(fn (Get $get): string => $get('use_barcode_search') && $get('merchant_product_id')
                                                        ? 'المنتج المختار'
                                                        : 'اسم المنتج')
                                                    ->required()
                                                    ->disabled(fn (Get $get): bool => $get('use_barcode_search') && (bool) $get('merchant_product_id'))
                                                    ->dehydrated(true)
                                                    ->visible(fn (Get $get): bool => ! $get('use_barcode_search') || (bool) $get('merchant_product_id'))
                                                    ->suffixAction(
                                                        Action::make('reset_barcode_search')
                                                            ->label('تغيير')
                                                            ->icon(Heroicon::OutlinedArrowPath)
                                                            ->visible(fn (Get $get): bool => $get('use_barcode_search') && (bool) $get('merchant_product_id'))
                                                            ->action(function (Set $set): void {
                                                                $set('merchant_product_id', null);
                                                                $set('barcode_search', null);
                                                                $set('product_name', null);
                                                                $set('unit_price', null);
                                                            }),
                                                    ),

                                                TextInput::make('quantity')
                                                    ->label('الكمية')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(0.01)
                                                    ->required()
                                                    ->live(),

                                                TextInput::make('unit_price')
                                                    ->label('السعر')
                                                    ->numeric()
                                                    ->prefix('ر.س')
                                                    ->required()
                                                    ->live(),
                                            ])
                                            ->columns(['default' => 1, 'md' => 4])
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull()
                                    ,
                            ]),

                        Section::make('الدفع والتسوية')
                            ->description('بيانات العميل وطريقة السداد')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->columnSpan(['default' => 1, 'xl' => 1])
                            ->schema([
                                Select::make('merchant_customer_id')
                                    ->label('العميل')
                                    ->options(fn () => MerchantCustomer::query()->pluck('name', 'id'))
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
                            ->columns(2)
                            ->columnSpanFull()
                            ,
                    ]),
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
                ->title('بيانات غير مكتملة')
                ->body($this->getCompleteSaleBlockReason() ?? 'يرجى تعبئة جميع الحقول المطلوبة')
                ->warning()
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
            'items' => [
                [
                    'quantity' => 1,
                    'use_barcode_search' => false,
                ],
            ],
        ]);
    }

    protected function handleRepeaterBarcodeScan(?string $state, Set $set): void
    {
        $code = $this->normalizeBarcode($state);

        if ($code === null) {
            return;
        }

        // تجاهل الإدخال الجزئي أثناء المسح — لا تنبيه ولا بحث
        if (strlen($code) < 3) {
            return;
        }

        $product = $this->findProductByCode($code);

        if ($product) {
            $set('merchant_product_id', $product->id);
            $set('product_name', $product->name);
            $set('unit_price', (float) $product->price);
            $set('barcode_search', null);

            return;
        }

        Notification::make()
            ->title('منتج غير موجود')
            ->body('لم يتم العثور على منتج بهذا الباركود أو الرمز')
            ->warning()
            ->send();
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

        return MerchantProduct::query()
            ->where('is_active', true)
            ->where(function ($query) use ($code): void {
                $query->where('barcode', $code)
                    ->orWhere('sku', $code);
            })
            ->first();
    }

    protected function appendProductToCart(Set $set, Get $get, MerchantProduct $product): void
    {
        $items = $get('items') ?? [];

        foreach ($items as $index => $item) {
            if (($item['merchant_product_id'] ?? null) == $product->id) {
                $items[$index]['quantity'] = ($items[$index]['quantity'] ?? 1) + 1;
                $set('items', array_values($items));

                return;
            }
        }

        $items[] = [
            'merchant_product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => $product->price,
            'use_barcode_search' => false,
        ];

        $set('items', array_values($items));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
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

    /**
     * @param  array<string, mixed>  $data
     */
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

    /**
     * @param  array<string, mixed>  $data
     */
    protected function netSaleTotal(array $data): float
    {
        $total = $this->calculateTotal($data['items'] ?? []);

        return max(0, $total - $this->calculateCreditApplied($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function maxPartialPaidAmount(array $data): ?float
    {
        $net = $this->netSaleTotal($data);

        if ($net <= 0.01) {
            return null;
        }

        return round($net - 0.01, 2);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function validItems(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item): bool => filled($item['product_name'] ?? null)
                && (float) ($item['quantity'] ?? 0) > 0
                && (float) ($item['unit_price'] ?? 0) > 0)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function validateSaleData(array $data): ?string
    {
        $items = $this->validItems($data['items'] ?? []);

        if ($items === []) {
            return 'أضف صنفاً واحداً على الأقل مع الاسم والكمية والسعر';
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
