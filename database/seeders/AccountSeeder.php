<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Enums\AccountTypes;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $accountTypes = AccountTypes::cases();

        echo "Starting account seeding process...\n";

        foreach ($users as $user) {
            $this->createAccountsForUser($user, $accountTypes);
        }

        echo "\nAccount seeding completed successfully.\n";
    }

    /**
     * Create accounts for a specific user.
     *
     * @param User $user
     * @param array $accountTypes
     * @return void
     */
    public function createForUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $accountTypes = AccountTypes::cases();
        $this->createAccountsForUser($user, $accountTypes);
    }

    /**
     * Helper method to create accounts for a user.
     *
     * @param User $user
     * @param array $accountTypes
     * @return void
     */
    private function createAccountsForUser(User $user, array $accountTypes): void
    {
        $numAccounts = rand(2, min(5, count($accountTypes)));
        echo "\nCreating {$numAccounts} accounts for user: {$user->name} (ID: {$user->id})\n";

        // Get random unique account types
        $selectedTypes = $accountTypes;
        shuffle($selectedTypes);
        $selectedTypes = array_slice($selectedTypes, 0, $numAccounts);

        foreach ($selectedTypes as $accountType) {
            $balance = rand(1000, 10000);
            $accountNumber = str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            
            $account = Account::create([
                'user_id' => $user->id,
                'account_type' => $accountType->value,
                'current_balance' => $balance,
                'account_number' => $accountNumber,
            ]);

            echo "  - Created account: Type: {$accountType->value}, Balance: \${$balance}, Account #: {$accountNumber}\n";
        }
    }
}
