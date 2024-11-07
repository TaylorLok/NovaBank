<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\AccountTrasanction;
use App\Enums\TransactionTypes;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all();
        $transactionTypes = [TransactionTypes::DEBIT, TransactionTypes::CREDIT];

        foreach ($accounts as $account) {
            $currentBalance = $account->current_balance;
            $numTransactions = rand(30, 50);
            $date = Carbon::now()->subMonths(6);

            echo "Seeding transactions for Account ID {$account->id}...\n";

            for ($i = 0; $i < $numTransactions; $i++) {
                $transactionType = rand(1, 100) <= 75 ? TransactionTypes::DEBIT : TransactionTypes::CREDIT;
                $isCredit = $transactionType === TransactionTypes::CREDIT;

                if ($isCredit) {
                    // For credit transactions, amount is 80% of current balance, rounded to the nearest integer
                    $amount = round($currentBalance * 0.8);
                } else {
                    // For debit transactions, use a random amount
                    $amount = rand(100, 500);
                }

                // Ensure absolute value for amount (no negative amounts)
                $amount = abs($amount);

                // Update balance based on transaction type
                if (!$isCredit) {
                    $currentBalance = max($currentBalance - $amount, 0);
                } else {
                    $currentBalance += $amount;
                }

                // Save the transaction with balance after transaction
                AccountTrasanction::create([
                    'account_id' => $account->id,
                    'transaction_type' => $transactionType->value,
                    'amount' => $amount,
                    'balance_after_transaction' => $currentBalance,
                    'created_at' => $date,
                ]);

                echo "  - Transaction #{$i} (Account ID {$account->id}): {$transactionType->value} of {$amount} on {$date->toDateString()} with balance: {$currentBalance}\n";

                // Move date forward with random gaps
                $date->addDays(rand(1, 3));
            }

            // Update the account's balance at the end of seeding
            $account->update(['current_balance' => $currentBalance]);

            echo "Seeding completed for Account ID {$account->id}, final balance: {$currentBalance}\n";
        }
    }
}
