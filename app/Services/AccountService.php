<?php

namespace App\Services;

use App\Interfaces\AccountServiceInterface;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Database\Seeders\AccountSeeder;
use Database\Seeders\TransactionSeeder;

class AccountService implements AccountServiceInterface
{
    public function getAllUserAccounts(int $userId): Collection
    {
        return Account::where('user_id', $userId)
            ->withSum('transactions as total_balance', DB::raw('
                CASE 
                    WHEN transaction_type = "credit" THEN amount 
                    ELSE -amount 
                END
            '))->get();
    }

    public function getAccountForUser(int $userId, int $accountId): ?Account
    {
        try 
        {
            // Attempt to find the account
            return Account::where('user_id', $userId)->findOrFail($accountId); 
        } 
        catch (ModelNotFoundException $e) {
            \Log::error("Account not found for user $userId and account $accountId: " . $e->getMessage());
            return null; 
        }
    }

    public function getDailyBalanceSummary(int $userId, int $accountId, string $startDate, string $endDate): Collection
    {
        // First verify account ownership
        $account = $this->getAccountForUser($userId, $accountId);
        
        if (!$account) {
            return collect([]); 
        }

        // Get initial balance before start date
        $initialBalance = DB::table('account_trasanctions')
            ->where('account_id', $accountId)
            ->where('created_at', '<', $startDate)
            ->sum(DB::raw('CASE WHEN transaction_type = "credit" THEN amount ELSE -amount END'));

        // Get all transactions between the date range
        $transactions = DB::table('account_trasanctions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN transaction_type = "debit" THEN amount ELSE 0 END) as total_debits'),
                DB::raw('SUM(CASE WHEN transaction_type = "credit" THEN amount ELSE 0 END) as total_credits')
            )
            ->where('account_id', $accountId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Create complete date range
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $result = collect();
        $runningBalance = $initialBalance;

        foreach ($dateRange as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayData = $transactions->firstWhere('date', $dateStr);

            $debits = $dayData?->total_debits ?? 0;
            $credits = $dayData?->total_credits ?? 0;

            $result->push([
                'date' => $dateStr,
                'opening_balance' => $runningBalance,
                'total_debits' => $debits,
                'total_credits' => $credits,
                'closing_balance' => $runningBalance - $debits + $credits
            ]);

            $runningBalance = $runningBalance - $debits + $credits;
        }

        return $result;
    }

    public function generateAccountsForNewUser(int $userId): void
    {
        // Generate accounts and transactions for a new user
        (new AccountSeeder)->createForUser($userId);
        (new TransactionSeeder)->createForUser($userId);
    }
}
