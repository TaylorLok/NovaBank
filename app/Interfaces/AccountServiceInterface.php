<?php

namespace App\Interfaces;

use App\Models\Account;
use Illuminate\Support\Collection;

interface AccountServiceInterface
{
    /**
     * Get all accounts for a specific user.
     *
     * @param int $userId
     * @return \Illuminate\Suuport\Collection
     */
    public function getAllUserAccounts(int $userId): Collection;

    /**
     * Get a specific account for a user.
     *
     * @param int $userId
     * @param int $accountId
     * @return \App\Models\Account|null
     */
    public function getAccountForUser(int $userId, int $accountId): ?Account;

    /**
     * Get the daily balance summary for a user's account between specific dates.
     *
     * @param int $userId
     * @param int $accountId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getDailyBalanceSummary(int $userId, int $accountId, string $startDate, string $endDate): Collection;

    /**
     * Generate accounts for a new user.
     *
     * @param int $userId
     * 
     */
    public function generateAccountsForNewUser(int $userId): void;
}

