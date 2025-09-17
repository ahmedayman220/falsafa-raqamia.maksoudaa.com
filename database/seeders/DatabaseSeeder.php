<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            OrdersSeeder::class,
            // Uncomment the line below to run big data seeding (WARNING: This will create 10,000+ records)
            // BigDataSeeder::class,
        ]);
    }
}
