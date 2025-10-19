# Repositories

This directory contains repository classes that handle data access logic and business operations.

---

## UserMerchantRepository

Repository for managing User Merchants and their balances.

### Methods

#### `find(int $merchantId): ?UserMerchant`

Find a merchant by ID.

#### `getBalance(int $merchantId): float`

Get the current balance of a merchant.

#### `exists(int $merchantId): bool`

Check if a merchant exists.

#### `updateBalance(int $merchantId, float $newBalance): bool`

Update the merchant's balance.

#### `validatePaymentAmount(int $merchantId, float $paymentAmount): array`

Validate if a payment amount is valid for the merchant. Returns:

```php
[
    'valid' => bool,
    'error' => string|null,
    'currentBalance' => float
]
```

**Example:**

```php
$repository = app(UserMerchantRepository::class);
$validation = $repository->validatePaymentAmount($merchantId, 1000.00);

if (!$validation['valid']) {
    // Handle error: $validation['error']
}
```

---

## UserMerchantOrderRepository

Repository for managing User Merchant Orders.

### Methods

#### `generateOrderNumber(int $userId): string`

Generate the next sequential order number for a specific user (7 digits, zero-padded).

#### `find(int $orderId): ?UserMerchantOrder`

Find an order by ID.

#### `getUserOrders(int $userId)`

Get all orders for a specific user.

#### `getMerchantOrders(int $merchantId)`

Get all orders for a specific merchant.

---

## UserMerchantOrderItemRepository

Repository for managing User Merchant Order Items.

### Methods

#### `createOrderItems(int $orderId, array $items): void`

Create multiple order items for an order in bulk.

#### `getOrderItems(int $orderId)`

Get all items for a specific order.

#### `calculateTotalPrice(array $items): float`

Calculate the total price from an array of order items.

---

## UserMerchantAccountEntryRepository

Repository for managing User Merchant Account Entries.

### Methods

#### `generateEntryNumber(): string`

Generate the next sequential entry number (7 digits, zero-padded).

#### `createPaymentEntry(UserMerchantPaymentTransaction $transaction, float $balanceAfter): UserMerchantAccountEntry`

Create an account entry for a payment transaction.

#### `createOrderEntry(UserMerchantOrder $order, float $balanceAfter): UserMerchantAccountEntry`

Create an account entry for an order transaction.

#### `getMerchantEntries(int $merchantId)`

Get all account entries for a merchant, ordered by date (descending).

#### `create(array $data): UserMerchantAccountEntry`

Create a new account entry with provided data.

**Example:**

```php
$repository = app(UserMerchantAccountEntryRepository::class);
$entry = $repository->createPaymentEntry($transaction, $newBalance);
$entry = $repository->createOrderEntry($order, $newBalance);
```

---

## UserMerchantAccountStatementRepository

Repository for managing User Merchant Account Statements.

### Methods

#### `createOpeningStatement(UserMerchant $merchant): ?UserMerchantAccountStatement`

Creates an opening account statement for a new merchant if one doesn't exist.

-   Returns `null` if statement already exists
-   Automatically handles debit/credit based on balance
-   Sets transaction type as 'adjustment'

**Example:**

```php
$repository = app(UserMerchantAccountStatementRepository::class);
$statement = $repository->createOpeningStatement($merchant);
```

#### `hasExistingStatement(int $merchantId): bool`

Checks if a merchant has any existing account statements.

#### `getMerchantStatements(int $merchantId)`

Retrieves all account statements for a merchant, ordered by transaction date (descending).

#### `getCurrentBalance(int $merchantId): float`

Gets the current balance from the most recent statement.

#### `create(array $data): UserMerchantAccountStatement`

Creates a new account statement with provided data.

#### `updateForPayment(int $merchantId, float $paymentAmount): bool`

Update the latest statement for a payment transaction (reduces balance and increases debit).

#### `updateForOrder(int $merchantId, float $orderAmount): bool`

Update the latest statement for an order transaction (increases balance and credit).

#### `getLatestStatement(int $merchantId): ?UserMerchantAccountStatement`

Get the most recent statement for a merchant.

---

# Services

## OrderService

Service for handling order operations. Coordinates multiple repositories to process orders.

### Methods

#### `generateOrderNumber(int $userId): string`

Generate a unique order number for a user.

#### `calculateTotalPrice(array $items): float`

Calculate the total price from order items array.

#### `processOrder(UserMerchantOrder $order, array $orderItems): void`

Process an order after creation:

-   Creates order items
-   Creates account entry
-   Updates merchant balance
-   Updates account statement

**Example:**

```php
$orderService = app(OrderService::class);

// Generate order number
$orderNumber = $orderService->generateOrderNumber($userId);

// Calculate total price
$totalPrice = $orderService->calculateTotalPrice($orderItems);

// Process after creating order
$orderService->processOrder($order, $orderItems);
```

---

## PaymentTransactionService

Service for handling payment transaction operations. Coordinates multiple repositories to process payments.

### Methods

#### `validatePayment(int $merchantId, float $paymentAmount): array`

Validate if a payment can be processed. Returns:

```php
[
    'valid' => bool,
    'error' => string|null,
    'currentBalance' => float
]
```

#### `processPayment(UserMerchantPaymentTransaction $transaction): void`

Process a completed payment transaction:

-   Creates account entry
-   Updates merchant balance
-   Updates account statement

**Example:**

```php
$paymentService = app(PaymentTransactionService::class);

// Validate before creating
$validation = $paymentService->validatePayment($merchantId, $amount);
if (!$validation['valid']) {
    // Handle error
}

// Process after creating transaction
$paymentService->processPayment($transaction);
```

#### `merchantExists(int $merchantId): bool`

Check if a merchant exists.

---

## Usage in Controllers/Pages

### In Regular Controllers

```php
use App\Repositories\UserMerchantAccountStatementRepository;

class YourController
{
    public function __construct(
        protected UserMerchantAccountStatementRepository $accountStatementRepository
    ) {}

    public function yourMethod()
    {
        $this->accountStatementRepository->createOpeningStatement($merchant);
    }
}
```

### In Livewire/Filament Pages

**Note:** Livewire components cannot use constructor injection. Use the `app()` helper directly:

```php
use App\Repositories\UserMerchantAccountStatementRepository;

class CreateUserMerchant extends CreateRecord
{
    protected function afterCreate(): void
    {
        // Use app() helper directly
        app(UserMerchantAccountStatementRepository::class)->createOpeningStatement($this->record);
    }
}
```

## Benefits

-   **Separation of Concerns**: Business logic is separated from controllers/pages
-   **Reusability**: Methods can be used across different parts of the application
-   **Testability**: Easier to mock and test
-   **Maintainability**: Changes to data access logic only need to be made in one place
