<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserMerchant;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchantOrder;
use App\Models\UserMerchantOrderItem;

class MerchantDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first user
        $user = User::first();
        
        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        // Get or create the current team
        $team = $user->teams()->first();
        if (!$team) {
            // Try to find existing team first
            $team = \App\Models\Team::where('slug', 'default-team')->first();
            
            if (!$team) {
                // Create a default team
                $team = \App\Models\Team::create([
                    'name' => 'Default Team',
                    'slug' => 'default-team',
                ]);
                
                $this->command->info('Created default team: ' . $team->name);
            }
            
            // Add user to team if not already a member
            if (!$team->members()->where('user_id', $user->id)->exists()) {
                $team->members()->attach($user->id, ['role' => 'owner']);
                $this->command->info('Added user to team');
            }
        }

        // Create merchants (check if they exist first)
        $merchant1 = UserMerchant::firstOrCreate(
            ['email' => 'electronics@merchant.com'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'name' => 'تاجر الأجهزة الإلكترونية',
                'phone' => '0501234567',
                'information' => 'متجر متخصص في الأجهزة الإلكترونية',
                'is_active' => true,
                'balance' => 0,
            ]
        );

        $merchant2 = UserMerchant::firstOrCreate(
            ['email' => 'fashion@merchant.com'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'name' => 'تاجر الملابس والأزياء',
                'phone' => '0501234568',
                'information' => 'متجر متخصص في الملابس والأزياء',
                'is_active' => true,
                'balance' => 0,
            ]
        );

        $merchant3 = UserMerchant::firstOrCreate(
            ['email' => 'home@merchant.com'],
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'name' => 'تاجر الأدوات المنزلية',
                'phone' => '0501234569',
                'information' => 'متجر متخصص في الأدوات المنزلية',
                'is_active' => true,
                'balance' => 0,
            ]
        );

        // Create products for merchant 1
        $products1 = [
            ['name' => 'هاتف آيفون 15', 'price' => 4500, 'barcode' => '1234567890', 'brand' => 'Apple'],
            ['name' => 'سامسونج جالاكسي S24', 'price' => 3800, 'barcode' => '1234567891', 'brand' => 'Samsung'],
            ['name' => 'لابتوب ديل', 'price' => 3200, 'barcode' => '1234567892', 'brand' => 'Dell'],
            ['name' => 'تابلت آيباد', 'price' => 2800, 'barcode' => '1234567893', 'brand' => 'Apple'],
        ];

        foreach ($products1 as $product) {
            UserMerchantProduct::create([
                'team_id' => $team->id,
                'user_merchant_id' => $merchant1->id,
                'name' => $product['name'],
                'price' => $product['price'],
                'barcode' => $product['barcode'],
                'brand' => $product['brand'],
                'is_active' => true,
            ]);
        }

        // Create products for merchant 2
        $products2 = [
            ['name' => 'قميص رجالي', 'price' => 150, 'barcode' => '2234567890', 'brand' => 'Zara'],
            ['name' => 'فستان نسائي', 'price' => 280, 'barcode' => '2234567891', 'brand' => 'H&M'],
            ['name' => 'جينز أزرق', 'price' => 200, 'barcode' => '2234567892', 'brand' => 'Levi\'s'],
            ['name' => 'حذاء رياضي', 'price' => 350, 'barcode' => '2234567893', 'brand' => 'Nike'],
        ];

        foreach ($products2 as $product) {
            UserMerchantProduct::create([
                'team_id' => $team->id,
                'user_merchant_id' => $merchant2->id,
                'name' => $product['name'],
                'price' => $product['price'],
                'barcode' => $product['barcode'],
                'brand' => $product['brand'],
                'is_active' => true,
            ]);
        }

        // Create products for merchant 3
        $products3 = [
            ['name' => 'غسالة أطباق', 'price' => 1800, 'barcode' => '3234567890', 'brand' => 'LG'],
            ['name' => 'ثلاجة سامسونج', 'price' => 2500, 'barcode' => '3234567891', 'brand' => 'Samsung'],
            ['name' => 'مكيف هواء', 'price' => 1200, 'barcode' => '3234567892', 'brand' => 'Carrier'],
            ['name' => 'فرن كهربائي', 'price' => 800, 'barcode' => '3234567893', 'brand' => 'Bosch'],
        ];

        foreach ($products3 as $product) {
            UserMerchantProduct::create([
                'team_id' => $team->id,
                'user_merchant_id' => $merchant3->id,
                'name' => $product['name'],
                'price' => $product['price'],
                'barcode' => $product['barcode'],
                'brand' => $product['brand'],
                'is_active' => true,
            ]);
        }

        // Create some orders
        $order1 = UserMerchantOrder::create([
            'team_id' => $team->id,
            'user_merchant_id' => $merchant1->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-001',
            'total_price' => 4500,
        ]);

        $order2 = UserMerchantOrder::create([
            'team_id' => $team->id,
            'user_merchant_id' => $merchant2->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-002',
            'total_price' => 430,
        ]);

        $order3 = UserMerchantOrder::create([
            'team_id' => $team->id,
            'user_merchant_id' => $merchant3->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-003',
            'total_price' => 1800,
        ]);

        // Create order items
        $product1 = UserMerchantProduct::where('user_merchant_id', $merchant1->id)->first();
        UserMerchantOrderItem::create([
            'team_id' => $team->id,
            'user_merchant_order_id' => $order1->id,
            'user_merchant_product_id' => $product1->id,
            'quantity' => 1,
            'price' => 4500,
            'total_price' => 4500,
        ]);

        $product2 = UserMerchantProduct::where('user_merchant_id', $merchant2->id)->first();
        UserMerchantOrderItem::create([
            'team_id' => $team->id,
            'user_merchant_order_id' => $order2->id,
            'user_merchant_product_id' => $product2->id,
            'quantity' => 2,
            'price' => 150,
            'total_price' => 300,
        ]);

        $product3 = UserMerchantProduct::where('user_merchant_id', $merchant3->id)->first();
        UserMerchantOrderItem::create([
            'team_id' => $team->id,
            'user_merchant_order_id' => $order3->id,
            'user_merchant_product_id' => $product3->id,
            'quantity' => 1,
            'price' => 1800,
            'total_price' => 1800,
        ]);

        $this->command->info('تم إنشاء بيانات تجريبية بنجاح!');
        $this->command->info('تم إنشاء 3 تجار و 12 منتج و 3 طلبات');
    }
}
