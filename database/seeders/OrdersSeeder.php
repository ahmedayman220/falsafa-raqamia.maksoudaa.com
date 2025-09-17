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
            'Premium Subscription', 'Basic Plan', 'Enterprise License', 'Mobile App Access', 'API Credits',
            'Storage Upgrade', 'Analytics Package', 'Support Package', 'Custom Integration', 'White Label Solution',
            'Cloud Hosting', 'Database Service', 'CDN Access', 'SSL Certificate', 'Domain Registration',
            'Email Service', 'Backup Service', 'Monitoring Tools', 'Security Suite', 'Performance Optimization',
            'Load Balancing', 'Auto Scaling', 'Disaster Recovery', 'Compliance Tools', 'Audit Services'
        ];

        $currencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'SEK', 'NOK', 'DKK'];
        
        $statuses = ['pending', 'paid', 'failed', 'cancelled', 'refunded'];
        
        $orders = [];
        
        // Create 1000 orders with various configurations
        for ($i = 1; $i <= 1000; $i++) {
            $user = $users->random();
            $product = $products[array_rand($products)];
            $currency = $currencies[array_rand($currencies)];
            $status = $statuses[array_rand($statuses)];
            
            // Vary the amounts with realistic pricing
            $amount = match ($i % 5) {
                0 => rand(10, 50),      // Small amounts ($10-50)
                1 => rand(50, 150),     // Medium amounts ($50-150)
                2 => rand(150, 500),    // Large amounts ($150-500)
                3 => rand(500, 2000),   // Premium amounts ($500-2000)
                default => rand(2000, 10000), // Enterprise amounts ($2000-10000)
            };

            // Generate realistic timestamps (spread over last 6 months)
            $createdAt = now()->subDays(rand(1, 180))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            $updatedAt = $createdAt->copy()->addMinutes(rand(1, 60));

            $orderData = [
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => $status,
                'metadata' => [
                    'product' => $product,
                    'currency' => $currency,
                    'order_number' => 'ORD-' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'created_by' => 'seeder',
                    'notes' => 'Test order for webhook testing and performance analysis',
                    'source' => ['web', 'mobile', 'api'][array_rand(['web', 'mobile', 'api'])],
                    'campaign' => ['organic', 'paid', 'referral', 'social'][array_rand(['organic', 'paid', 'referral', 'social'])],
                ],
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            // Add external transaction ID for some orders (simulating webhook processed orders)
            if (in_array($status, ['paid', 'refunded']) && rand(1, 3) === 1) {
                $orderData['external_txn_id'] = 'TXN-' . strtoupper(uniqid());
            }

            $orders[] = $orderData;
        }

        // Insert orders in batches for better performance
        $chunks = array_chunk($orders, 100);
        foreach ($chunks as $chunk) {
            Order::insert($chunk);
        }

        $this->command->info('Created 1000 test orders');
    }
}
