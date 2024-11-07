<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed the default user from the .env variables
        $defaultUser = User::create([
            'name' => env('DEFAULT_USER_NAME', 'Default User'),
            'email' => env('DEFAULT_USER_EMAIL', 'default@example.com'),
            'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'password')),
        ]);

        echo "Created default user: {$defaultUser->name} ({$defaultUser->email})\n";

        // Seed 4 more random users
        echo "Creating 4 random users...\n";
        
        User::factory(4)->create()->each(function (User $user, int $index) {
            echo "Created random user #" . ($index + 1) . ": {$user->name} ({$user->email})\n";
        });

        echo "User seeding completed.\n";
    }
}
