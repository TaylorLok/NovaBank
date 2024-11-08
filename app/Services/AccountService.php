<?php

namespace App\Services;

use App\Interfaces\AccountServiceInterface;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Database\Seeders\AccountSeeder;
use Database\Seeders\TransactionSeeder;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

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
        // Verify account ownership
        $accountExists = DB::table('accounts')
            ->where('user_id', $userId)
            ->where('id', $accountId)
            ->exists();

        if (!$accountExists) {
            \Log::info("Account {$accountId} not found for user {$userId}");
            return new Collection();
        }

        // Prepare date range
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Log parameters for debugging
        \Log::info('Executing daily balance summary', [
            'accountId' => $accountId,
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
        ]);

        // Query with parameters
        $query = "
            WITH daily_transactions AS (
                SELECT 
                    DATE(created_at) AS date,
                    SUM(CASE WHEN transaction_type = 'DEBIT' THEN amount ELSE 0 END) AS total_debits,
                    SUM(CASE WHEN transaction_type = 'CREDIT' THEN amount ELSE 0 END) AS total_credits
                FROM account_transactions
                WHERE account_id = ?
                    AND created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
            ),
            cumulative_balance AS (
                SELECT
                    date,
                    total_debits,
                    total_credits,
                    COALESCE(
                        LAG(total_credits - total_debits) OVER (ORDER BY date),
                        (SELECT balance_after_transaction FROM account_transactions WHERE account_id = ? ORDER BY created_at ASC LIMIT 1)
                    ) AS opening_balance
                FROM daily_transactions
            )
            SELECT 
                date,
                opening_balance,
                total_debits,
                total_credits,
                (opening_balance - total_debits + total_credits) AS closing_balance
            FROM cumulative_balance
            ORDER BY date;
        ";

        // Execute the query
        $results = DB::select($query, [
            $accountId,
            $startDateTime,
            $endDateTime,
            $accountId
        ]);

        // Log results for debugging
        \Log::info('Daily balance summary results', ['results' => $results]);

        return collect($results)->map(function ($row) {
            return (object) [
                'date' => $row->date,
                'opening_balance' => round($row->opening_balance, 2),
                'total_debits' => round($row->total_debits, 2),
                'total_credits' => round($row->total_credits, 2),
                'closing_balance' => round($row->closing_balance, 2)
            ];
        });
    }


    public function generateAccountsForNewUser(int $userId): void
    {
        // Generate accounts and transactions for a new user
        (new AccountSeeder)->createForUser($userId);
        (new TransactionSeeder)->createForUser($userId);
    }
}
