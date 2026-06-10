<?php

use App\Models\User;
use App\Models\Team;
use App\Models\UserMerchant;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchantWallet;
use App\Models\UserMerchantOrder;
use App\Models\UserMerchantOrderItem;
use App\Models\UserMerchantAccountStatement;
use App\Models\UserMerchantAccountEntry;
use App\Models\UserMerchantPaymentTransaction;
use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Enums\UserType;
use App\Enums\ProductUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('blocks access to endpoints for unauthenticated users', function () {
    $this->getJson('/api/user-data/export')->assertStatus(401);
    $this->postJson('/api/user-data/import')->assertStatus(401);
    $this->deleteJson('/api/user-data/delete-account')->assertStatus(401);
});

it('exports user data with valid signature and structure', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => UserType::MERCHANT,
        'address' => 'My Address',
        'phone' => '123456789',
    ]);

    $team = Team::create(['name' => 'Team 1', 'slug' => 'team-1']);
    $team->members()->attach($user, ['role' => 'owner']);

    $merchant = UserMerchant::create([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'name' => 'Shop 1',
        'email' => 'shop1@example.com',
        'phone' => '555555',
        'is_active' => true,
        'balance' => 100.00
    ]);

    $wallet = UserMerchantWallet::create([
        'team_id' => $team->id,
        'user_merchant_id' => $merchant->id,
        'account_name' => 'Main Wallet',
        'bank_account_number' => 'SA1111',
        'bank_name' => 'Alinma',
        'is_active' => true,
    ]);

    $product = UserMerchantProduct::create([
        'team_id' => $team->id,
        'user_merchant_id' => $merchant->id,
        'name' => 'Water Bottle',
        'price' => 5.00,
        'barcode' => 'B001',
        'is_active' => true,
    ]);

    $order = UserMerchantOrder::create([
        'team_id' => $team->id,
        'user_merchant_id' => $merchant->id,
        'user_id' => $user->id,
        'order_number' => 'ORD-001',
        'total_price' => 5.00
    ]);

    $orderItem = UserMerchantOrderItem::create([
        'team_id' => $team->id,
        'user_merchant_order_id' => $order->id,
        'user_merchant_product_id' => $product->id,
        'unit' => ProductUnit::PIECE->value,
        'quantity' => 1.0,
        'price' => 5.00,
        'total_price' => 5.00
    ]);

    $response = $this->actingAs($user)->getJson('/api/user-data/export');

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/json');

    $contentDisposition = $response->headers->get('Content-Disposition');
    expect($contentDisposition)->toContain('attachment; filename="user_data_' . $user->id . '_');

    // Let's assert content structure:
    $data = $response->json();
    expect($data['user_id'])->toBe($user->id);
    expect($data['user']['name'])->toBe('John Doe');
    expect($data['merchants'])->toHaveCount(1);
    expect($data['merchants'][0]['name'])->toBe('Shop 1');
    expect($data['merchants'][0]['wallets'][0]['account_name'])->toBe('Main Wallet');
    expect($data['merchants'][0]['products'][0]['name'])->toBe('Water Bottle');
    expect($data['merchants'][0]['orders'][0]['order_number'])->toBe('ORD-001');

    // Verify signature integrity
    $signature = $data['signature'];
    unset($data['signature']);
    $dataString = json_encode($data, JSON_UNESCAPED_UNICODE);
    $expectedSignature = hash_hmac('sha256', $dataString, config('app.key'));
    expect($signature)->toBe($expectedSignature);
});

it('fails to import data if file validation fails', function () {
    $user = User::factory()->create();

    // No file
    $this->actingAs($user)->postJson('/api/user-data/import', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['data_file']);

    // Invalid file extension/mime
    $txtFile = UploadedFile::fake()->create('invalid.txt', 10, 'text/plain');
    $this->actingAs($user)->postJson('/api/user-data/import', ['data_file' => $txtFile])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['data_file']);
});

it('fails to import data if JSON content is invalid', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('user_data.json', 10, 'application/json');

    $this->actingAs($user)->postJson('/api/user-data/import', ['data_file' => $file])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid JSON file'
        ]);
});

it('fails to import data if data structure is missing required keys', function () {
    $user = User::factory()->create();

    // Missing signature/merchants
    $exportData = [
        'export_date' => now()->toISOString(),
        'export_version' => '1.0',
        'user_id' => $user->id,
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'import_test');
    file_put_contents($tempFile, json_encode($exportData));
    $file = new UploadedFile($tempFile, 'user_data.json', 'application/json', null, true);

    $this->actingAs($user)->postJson('/api/user-data/import', ['data_file' => $file])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid data structure'
        ]);
});

it('fails to import data if signature verification fails', function () {
    $user = User::factory()->create();

    $exportData = [
        'export_date' => now()->toISOString(),
        'export_version' => '1.0',
        'user_id' => $user->id,
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
        'merchants' => [],
        'signature' => 'invalid_signature_hash_value'
    ];

    $tempFile = tempnam(sys_get_temp_dir(), 'import_test');
    file_put_contents($tempFile, json_encode($exportData, JSON_UNESCAPED_UNICODE));
    $file = new UploadedFile($tempFile, 'user_data.json', 'application/json', null, true);

    $this->actingAs($user)->postJson('/api/user-data/import', ['data_file' => $file])
        ->assertStatus(500)
        ->assertJson([
            'success' => false,
        ]);
});

it('imports user data successfully with valid signature', function () {
    $user = User::factory()->create([
        'address' => 'Original Address',
        'phone' => '0000000',
    ]);

    $exportData = [
        'export_date' => now()->toISOString(),
        'export_version' => '1.0',
        'user_id' => $user->id,
        'user' => [
            'name' => 'Imported Name',
            'email' => $user->email,
            'address' => 'Updated Address',
            'phone' => '9999999',
        ],
        'merchants' => [
            [
                'name' => 'Imported Merchant',
                'email' => 'imp@example.com',
                'phone' => '123123',
                'is_active' => true,
                'balance' => 250.00,
                'products' => [
                    [
                        'name' => 'Imported Prod',
                        'price' => 99.99,
                        'is_active' => true,
                    ]
                ]
            ]
        ],
    ];

    $dataString = json_encode($exportData, JSON_UNESCAPED_UNICODE);
    $signature = hash_hmac('sha256', $dataString, config('app.key'));
    $exportData['signature'] = $signature;

    $tempFile = tempnam(sys_get_temp_dir(), 'import_test');
    file_put_contents($tempFile, json_encode($exportData, JSON_UNESCAPED_UNICODE));
    $file = new UploadedFile($tempFile, 'user_data.json', 'application/json', null, true);

    $response = $this->actingAs($user)->postJson('/api/user-data/import', ['data_file' => $file]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Data imported successfully'
        ]);

    // Verify user fields updated
    $user->refresh();
    expect($user->address)->toBe('Updated Address');
    expect($user->phone)->toBe('9999999');

    // Verify database entries created
    $this->assertDatabaseHas('user_merchants', [
        'user_id' => $user->id,
        'name' => 'Imported Merchant',
        'email' => 'imp@example.com'
    ]);

    $merchant = $user->merchants()->first();
    $this->assertDatabaseHas('user_merchant_products', [
        'user_merchant_id' => $merchant->id,
        'name' => 'Imported Prod',
        'price' => 99.99
    ]);
});

it('fails to delete account when password confirmation is missing or incorrect', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    // Missing password
    $this->actingAs($user)->deleteJson('/api/user-data/delete-account')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    // Incorrect password
    $this->actingAs($user)->deleteJson('/api/user-data/delete-account', ['password' => 'wrong-pass'])
        ->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Incorrect password'
        ]);

    // Ensure user was not deleted
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

it('deletes user account and cascades to all related merchants and data', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $team = Team::create(['name' => 'Team A', 'slug' => 'team-a']);
    $team->members()->attach($user, ['role' => 'owner']);

    $merchant = UserMerchant::create([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'name' => 'Del Merchant',
        'email' => 'del@example.com',
        'phone' => '123'
    ]);

    $product = UserMerchantProduct::create([
        'team_id' => $team->id,
        'user_merchant_id' => $merchant->id,
        'name' => 'Del Product',
        'price' => 10.00
    ]);

    $order = UserMerchantOrder::create([
        'team_id' => $team->id,
        'user_merchant_id' => $merchant->id,
        'user_id' => $user->id,
        'order_number' => 'ORD-DEL',
        'total_price' => 10.00
    ]);

    $orderItem = UserMerchantOrderItem::create([
        'team_id' => $team->id,
        'user_merchant_order_id' => $order->id,
        'user_merchant_product_id' => $product->id,
        'unit' => ProductUnit::PIECE->value,
        'quantity' => 1.0,
        'price' => 10.00,
        'total_price' => 10.00
    ]);

    $response = $this->actingAs($user)->deleteJson('/api/user-data/delete-account', [
        'password' => 'correct-password'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);

    // Check database has no trace of user or their related data
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertDatabaseMissing('user_merchants', ['id' => $merchant->id]);
    $this->assertDatabaseMissing('user_merchant_products', ['id' => $product->id]);
    $this->assertDatabaseMissing('user_merchant_orders', ['id' => $order->id]);
    $this->assertDatabaseMissing('user_merchant_order_items', ['id' => $orderItem->id]);

    // Check logged out
    expect(Auth::check())->toBeFalse();
});
