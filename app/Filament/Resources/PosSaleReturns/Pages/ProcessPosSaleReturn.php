<?php

namespace App\Filament\Resources\PosSaleReturns\Pages;

use App\Enums\RefundMethod;
use App\Enums\ReturnType;
use App\Filament\Resources\PosSaleReturns\PosSaleReturnResource;
use App\Models\MerchantProduct;
use App\Models\PosSale;
use App\Services\PosReturnService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class ProcessPosSaleReturn extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PosSaleReturnResource::class;
    protected string $view = 'filament.pages.process-pos-sale-return';

    public ?array $data = [];
    public ?PosSale $selectedSale = null;

    public function mount(): void
    {
        $this->form->fill([
            'return_type'   => ReturnType::RETURN->value,
            'refund_method' => RefundMethod::CASH->value,
        ]);
    }

    public function form($form)
    {
        return $form
            ->schema([
                // ── خطوة 1: البحث عن الفاتورة ──────────────────────────────────
                Section::make('البحث عن الفاتورة')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->schema([
                        TextInput::make('sale_number_search')
                            ->label('رقم الفاتورة')
                            ->placeholder('أدخل رقم الفاتورة...')
                            ->live(debounce: 400)
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (! $state) {
                                    $this->selectedSale = null;
                                    return;
                                }
                                $team = Filament::getTenant();
                                $sale = PosSale::where('team_id', $team->id)
                                    ->where('sale_number', ltrim($state, '0'))
                                    ->orWhere('sale_number', $state)
                                    ->with('items.merchantProduct')
                                    ->first();
                                $this->selectedSale = $sale;
                                if ($sale) {
                                    $set('loaded_sale_id', $sale->id);
                                    $set('merchant_customer_id', $sale->merchant_customer_id);
                                }
                            })
                            ->suffixIcon(Heroicon::OutlinedMagnifyingGlass),

                        Placeholder::make('sale_info')
                            ->label('بيانات الفاتورة')
                            ->visible(fn () => $this->selectedSale !== null)
                            ->content(function (): string {
                                $sale = $this->selectedSale;
                                if (! $sale) return '—';
                                return implode(' | ', array_filter([
                                    'رقم: '.$sale->sale_number,
                                    'التاريخ: '.$sale->created_at?->format('Y/m/d'),
                                    'العميل: '.($sale->merchantCustomer?->name ?? 'عميل غير مسجّل'),
                                    'الإجمالي: '.number_format($sale->total_amount, 2).' ر.س',
                                    'حالة: '.$sale->status,
                                ]));
                            })
                            ->columnSpanFull(),
                    ]),

                // ── خطوة 2: اختيار الأصناف المُرجَعة ──────────────────────────
                Section::make('أصناف الإرجاع')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->visible(fn () => $this->selectedSale !== null)
                    ->schema([
                        Repeater::make('return_items')
                            ->label('الأصناف المُرجَعة من الفاتورة')
                            ->addActionLabel('إضافة صنف للإرجاع')
                            ->live()
                            ->schema([
                                Select::make('pos_sale_item_id')
                                    ->label('الصنف من الفاتورة')
                                    ->options(function (): array {
                                        if (! $this->selectedSale) return [];
                                        return $this->selectedSale->items->mapWithKeys(fn ($item) => [
                                            $item->id => $item->product_name.' (كمية: '.$item->quantity.' — سعر: '.$item->unit_price.' ر.س)',
                                        ])->toArray();
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set): void {
                                        if (! $state || ! $this->selectedSale) return;
                                        $item = $this->selectedSale->items->find($state);
                                        if (! $item) return;
                                        
                                        $alreadyReturned = \App\Models\PosSaleReturnItem::where('pos_sale_item_id', $item->id)
                                            ->whereHas('saleReturn', fn ($q) => $q->where('status', 'completed'))
                                            ->sum('quantity_returned');
                                        $maxReturnable = (float) $item->quantity - (float) $alreadyReturned;

                                        $set('merchant_product_id', $item->merchant_product_id);
                                        $set('product_name', $item->product_name);
                                        $set('unit_price', (float) $item->unit_price);
                                        $set('unit_cost', $item->merchantProduct ? (float) $item->merchantProduct->cost : 0);
                                        $set('max_qty', max(0, $maxReturnable));
                                        $set('quantity_returned', min(1, max(0, $maxReturnable)));
                                    }),
 
                                TextInput::make('quantity_returned')
                                    ->label('الكمية المُرجَعة')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->maxValue(fn (Get $get) => (float) $get('max_qty'))
                                    ->validationMessages([
                                        'max' => 'الكمية المرجعة لا يمكن أن تتجاوز الكمية المتاحة في الفاتورة (:max).',
                                    ])
                                    ->required()
                                    ->live(),

                                TextInput::make('unit_price')
                                    ->label('سعر الوحدة')
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->disabled()
                                    ->dehydrated(true),

                                Select::make('item_condition')
                                    ->label('حالة الصنف')
                                    ->options([
                                        'resellable' => 'قابل لإعادة البيع',
                                        'damaged'    => 'تالف',
                                        'disposed'   => 'للإتلاف',
                                    ])
                                    ->default('resellable')
                                    ->required(),

                                Select::make('return_reason')
                                    ->label('سبب الإرجاع')
                                    ->options([
                                        'defective'    => 'معيب أو تالف',
                                        'changed_mind' => 'تغيير رأي',
                                        'wrong_item'   => 'صنف خاطئ',
                                        'other'        => 'أخرى',
                                    ]),

                                // حقول مخفية
                                TextInput::make('merchant_product_id')->hidden()->dehydrated(true),
                                TextInput::make('product_name')->hidden()->dehydrated(true),
                                TextInput::make('unit_cost')->hidden()->dehydrated(true),
                                TextInput::make('max_qty')->hidden()->dehydrated(true),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),

                        Placeholder::make('return_total')
                            ->label('إجمالي قيمة المُرجَع')
                            ->content(function (Get $get): string {
                                $items = $get('return_items') ?? [];
                                $total = collect($items)->sum(fn ($i) => (float) ($i['quantity_returned'] ?? 0) * (float) ($i['unit_price'] ?? 0));
                                return number_format($total, 2).' ر.س';
                            })
                            ->extraAttributes(['class' => 'text-xl font-bold text-danger-600']),
                    ]),

                // ── خطوة 3: اختيار نوع العملية ─────────────────────────────────
                Section::make('نوع العملية والتسوية')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->visible(fn () => $this->selectedSale !== null)
                    ->schema([
                        Select::make('return_type')
                            ->label('نوع العملية')
                            ->options(ReturnType::options())
                            ->required()
                            ->live()
                            ->default(ReturnType::RETURN->value),

                        Select::make('refund_method')
                            ->label('طريقة الرد / التسوية')
                            ->options(function (Get $get): array {
                                $type = $get('return_type');
                                if ($type === ReturnType::EXCHANGE->value) {
                                    return [
                                        RefundMethod::CASH->value        => 'استرداد الفرق نقداً',
                                        RefundMethod::CREDIT_NOTE->value => 'رصيد دائن للعميل',
                                        RefundMethod::NONE->value        => 'بدون استرداد (نفس القيمة)',
                                    ];
                                }
                                return [
                                    RefundMethod::CASH->value        => 'نقد للعميل',
                                    RefundMethod::CREDIT_NOTE->value => 'رصيد دائن للعميل',
                                ];
                            })
                            ->default(RefundMethod::CASH->value)
                            ->required()
                            ->live(),

                        Select::make('merchant_customer_id')
                            ->label('العميل المستلم للرصيد الدائن')
                            ->options(fn () => \App\Models\MerchantCustomer::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get): bool => $get('refund_method') === RefundMethod::CREDIT_NOTE->value)
                            ->required(fn (Get $get): bool => $get('refund_method') === RefundMethod::CREDIT_NOTE->value)
                            ->default(fn () => $this->selectedSale?->merchant_customer_id)
                            ->disabled(fn () => ! empty($this->selectedSale?->merchant_customer_id))
                            ->dehydrated(true)
                            ->helperText(fn () => ! empty($this->selectedSale?->merchant_customer_id)
                                ? 'تم تحديد العميل تلقائياً من الفاتورة الأصلية'
                                : 'الفاتورة لعميل نقدي/غير مسجّل. يجب تحديد أو إنشاء عميل لتسجيل الرصيد الدائن باسمه.')
                            ->createOptionForm([
                                TextInput::make('name')->label('الاسم')->required(),
                                TextInput::make('phone')->label('الهاتف'),
                            ])
                            ->createOptionUsing(fn (array $data) => \App\Models\MerchantCustomer::create($data)->id)
                            ->columnSpanFull(),

                        // ── أصناف الاستبدال (تظهر فقط عند الاستبدال) ──────────
                        Section::make('أصناف الاستبدال')
                            ->icon(Heroicon::OutlinedArrowsRightLeft)
                            ->visible(fn (Get $get): bool => $get('return_type') === ReturnType::EXCHANGE->value)
                            ->schema([
                                Repeater::make('exchange_items')
                                    ->label('الأصناف البديلة')
                                    ->addActionLabel('إضافة صنف بديل')
                                    ->live()
                                    ->schema([
                                        Select::make('merchant_product_id')
                                            ->label('المنتج البديل')
                                            ->options(fn () => MerchantProduct::where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set): void {
                                                if (! $state) return;
                                                $product = MerchantProduct::find($state);
                                                if ($product) {
                                                    $set('product_name', $product->name);
                                                    $set('unit_price', (float) $product->price);
                                                    $set('unit_cost', (float) $product->cost);
                                                }
                                            }),

                                        TextInput::make('quantity')
                                            ->label('الكمية')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->default(1)
                                            ->required()
                                            ->live(),

                                        TextInput::make('unit_price')
                                            ->label('سعر الوحدة')
                                            ->numeric()
                                            ->prefix('ر.س')
                                            ->required()
                                            ->live(),

                                        TextInput::make('product_name')->hidden()->dehydrated(true),
                                        TextInput::make('unit_cost')->hidden()->dehydrated(true),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),

                                // ملخص الفارق
                                Placeholder::make('exchange_summary')
                                    ->label('ملخص الاستبدال')
                                    ->columnSpanFull()
                                    ->content(function (Get $get): string {
                                        $returnItems   = $get('return_items') ?? [];
                                        $exchangeItems = $get('exchange_items') ?? [];

                                        $returnTotal   = collect($returnItems)->sum(fn ($i) => (float) ($i['quantity_returned'] ?? 0) * (float) ($i['unit_price'] ?? 0));
                                        $exchangeTotal = collect($exchangeItems)->sum(fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));
                                        $diff = $exchangeTotal - $returnTotal;

                                        $parts = [
                                            'قيمة المُرجَع: '.number_format($returnTotal, 2).' ر.س',
                                            'قيمة البديل: '.number_format($exchangeTotal, 2).' ر.س',
                                        ];

                                        if ($diff > 0) {
                                            $parts[] = '⬆ العميل يدفع: '.number_format($diff, 2).' ر.س';
                                        } elseif ($diff < 0) {
                                            $parts[] = '⬇ يُستردّ للعميل: '.number_format(abs($diff), 2).' ر.س';
                                        } else {
                                            $parts[] = '✓ تسوية بلا فارق';
                                        }

                                        return implode("\n", $parts);
                                    }),
                            ])
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function processReturn(): void
    {
        $data = $this->form->getState();
        $team = Filament::getTenant();

        if (! $this->selectedSale) {
            Notification::make()->title('لم يتم اختيار فاتورة')->danger()->send();
            return;
        }

        if (empty($data['return_items'])) {
            Notification::make()->title('يجب اختيار صنف واحد على الأقل للإرجاع')->danger()->send();
            return;
        }

        try {
            $returnType   = ReturnType::from($data['return_type']);
            $refundMethod = RefundMethod::from($data['refund_method'] ?? RefundMethod::CASH->value);
            $service      = app(PosReturnService::class);

            $returnItems = collect($data['return_items'])->map(fn ($item) => [
                'pos_sale_item_id'    => $item['pos_sale_item_id'] ?? null,
                'merchant_product_id' => $item['merchant_product_id'] ?? null,
                'product_name'        => $item['product_name'] ?? '',
                'quantity_returned'   => (float) ($item['quantity_returned'] ?? 0),
                'unit_price'          => (float) ($item['unit_price'] ?? 0),
                'unit_cost'           => (float) ($item['unit_cost'] ?? 0),
                'return_reason'       => $item['return_reason'] ?? null,
                'item_condition'      => $item['item_condition'] ?? 'resellable',
            ])->toArray();

            if ($returnType === ReturnType::EXCHANGE) {
                if (empty($data['exchange_items'])) {
                    Notification::make()->title('يجب إضافة صنف بديل للاستبدال')->danger()->send();
                    return;
                }
                $exchangeItems = collect($data['exchange_items'])->map(fn ($item) => [
                    'merchant_product_id' => $item['merchant_product_id'] ?? null,
                    'product_name'        => $item['product_name'] ?? '',
                    'quantity'            => (float) ($item['quantity'] ?? 0),
                    'unit_price'          => (float) ($item['unit_price'] ?? 0),
                    'unit_cost'           => (float) ($item['unit_cost'] ?? 0),
                ])->toArray();

                $result = $service->processExchange(
                    $team,
                    $this->selectedSale,
                    $returnItems,
                    $exchangeItems,
                    $refundMethod,
                    $data['notes'] ?? null,
                    $data['merchant_customer_id'] ?? null
                );
            } else {
                $result = $service->processReturn(
                    $team,
                    $this->selectedSale,
                    $returnItems,
                    $refundMethod,
                    $data['notes'] ?? null,
                    $data['merchant_customer_id'] ?? null
                );
            }

            Notification::make()
                ->title('تمت العملية بنجاح')
                ->body('رقم المستند: '.$result->return_number)
                ->success()
                ->send();

            $this->selectedSale = null;
            $this->form->fill([
                'return_type'   => ReturnType::RETURN->value,
                'refund_method' => RefundMethod::CASH->value,
            ]);

        } catch (\Throwable $e) {
            Notification::make()
                ->title('خطأ في المعالجة')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('process')
                ->label('تأكيد وترحيل')
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تأكيد الإرجاع / الاستبدال')
                ->modalDescription('سيتم ترحيل القيود المحاسبية وتحديث المخزون. هذا الإجراء لا يمكن التراجع عنه.')
                ->action(fn () => $this->processReturn())
                ->visible(fn () => $this->selectedSale !== null),
        ];
    }

    public function getTitle(): string
    {
        return 'إرجاع / استبدال بضاعة';
    }
}
