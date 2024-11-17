<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Enums\AccountTypes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(), 
            'account_type' => AccountTypes::SAVINGS, 
            'current_balance' => $this->faker->randomFloat(2, 100, 10000), 
            'account_number' => $this->faker->unique()->bankAccountNumber(), 
        ];
    }
}
