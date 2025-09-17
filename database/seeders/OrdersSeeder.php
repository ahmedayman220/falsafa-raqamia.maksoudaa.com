<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UsersSeeder first.');
            return;
        }

        $products = [
            'Premium Subscription',
            'Basic Plan',
            'Enterprise License',
            'Mobile App Access',
            'API Credits',
            'Storage Upgrade',
            'Analytics Package',
            'Support Package',
            'Custom Integration',
            'White Label Solution',
        ];

        $currencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];
        
        $orders = [];
        
        // Create 15 orders with various configurations
        for ($i = 1; $i <= 15; $i++) {
            $user = $users->random();
            $product = $products[array_rand($products)];
            $currency = $currencies[array_rand($currencies)];
            
            // Vary the amounts
            $amount = match ($i % 4) {
                0 => rand(10, 50),      // Small amounts
                1 => rand(50, 150),     // Medium amounts
                2 => rand(150, 500),    // Large amounts
                default => rand(500, 2000), // Premium amounts
            };

            $orderData = [
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'pending',
                'metadata' => [
                    'product' => $product,
                    'currency' => $currency,
                    'order_number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'created_by' => 'seeder',
                    'notes' => 'Test order for webhook testing',
                ],
            ];

            // Some orders (about 30%) will have external_txn_id for testing
            if ($i % 3 === 0) {
                $orderData['external_txn_id'] = 'tx_seeded_' . str_pad($i, 6, '0', STR_PAD_LEFT);
            }

            $orders[] = $orderData;
        }

        foreach ($orders as $orderData) {
            Order::create($orderData);
        }

        $this->command->info('Created ' . count($orders) . ' test orders');
        
        // Show summary
        $ordersWithTxnId = Order::whereNotNull('external_txn_id')->count();
        $ordersWithoutTxnId = Order::whereNull('external_txn_id')->count();
        
        $this->command->info("Orders with external_txn_id: {$ordersWithTxnId}");
        $this->command->info("Orders without external_txn_id: {$ordersWithoutTxnId}");
    }
}
