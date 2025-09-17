<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = [
            'John', 'Jane', 'Mike', 'Sarah', 'David', 'Emily', 'Chris', 'Lisa', 'Tom', 'Anna',
            'Alex', 'Maria', 'James', 'Emma', 'Ryan', 'Sophie', 'Daniel', 'Olivia', 'Mark', 'Grace',
            'Kevin', 'Rachel', 'Steve', 'Laura', 'Paul', 'Kate', 'Ben', 'Amy', 'Nick', 'Helen',
            'Sam', 'Julia', 'Rob', 'Nina', 'Tim', 'Eva', 'Matt', 'Claire', 'Luke', 'Maya',
            'Jake', 'Zoe', 'Adam', 'Lily', 'Josh', 'Ruby', 'Max', 'Chloe', 'Noah', 'Ella'
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
            'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
            'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'
        ];

        $domains = ['example.com', 'test.com', 'demo.org', 'sample.net', 'mock.io'];

        $users = [];
        
        // Generate 100 users
        for ($i = 1; $i <= 100; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $domain = $domains[array_rand($domains)];
            
            // Ensure unique emails
            $email = strtolower($firstName . '.' . $lastName . $i . '@' . $domain);
            
            $users[] = [
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'password' => Hash::make('password123'),
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now()->subDays(rand(1, 365)),
            ];
        }

        // Insert users in batches for better performance
        User::insert($users);

        $this->command->info('Created 100 test users');
    }
}
