<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Enums\AccountTypes;
use App\Enums\TransactionTypes;
use App\Services\AccountService; 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and account for each test
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_type' => AccountTypes::SAVINGS,
            'current_balance' => 1000.00
        ]);
    }

    /**
     * Test that an unauthenticated user cannot access accounts.
     */
    public function test_unauthenticated_user_cannot_access_accounts()
    {
        $response = $this->get(route('accounts.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test to ensure that a user can only see their own accounts.
     */
    public function test_user_can_see_only_their_accounts()
    {
        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('accounts.index'));
            
        $response->assertStatus(200)
            ->assertSee($this->account->account_number)
            ->assertDontSee($otherAccount->account_number);
    }

    /**
     * Test that daily balance calculations are correct.
     */
    public function test_daily_balance_calculations_are_correct()
    {
        $this->actingAs($this->user);

        AccountTransaction::create([ 
            'account_id' => $this->account->id,
            'transaction_type' => TransactionTypes::DEBIT,
            'amount' => 200.00,
            'created_at' => '2024-01-01',
            'balance_after_transaction' => 800.00
        ]);

        AccountTransaction::create([ 
            'account_id' => $this->account->id,
            'transaction_type' => TransactionTypes::CREDIT,
            'amount' => 400.00,
            'created_at' => '2024-01-01',
            'balance_after_transaction' => 1200.00
        ]);

        $response = $this->getJson(route('accounts.dailyBalance', [
            'account' => $this->account->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-01'
        ]));

        $response->assertStatus(200)
            ->assertJson([ 
                [
                    'date' => '2024-01-01',
                    'opening_balance' => 1000.00,
                    'total_debits' => 200.00,
                    'total_credits' => 400.00,
                    'closing_balance' => 1200.00
                ]
            ]);
    }

    /**
     * Test that a user can only see their own data.
     */
    public function test_user_can_only_see_their_own_data()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $accountA = Account::factory()->create(['user_id' => $userA->id]);
        $accountB = Account::factory()->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)
            ->get(route('accounts.index'));

        $response->assertSee($accountA->account_number)
            ->assertDontSee($accountB->account_number);
    }

    public function test_user_cannot_access_other_users_account_details()
    {
        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user);

        $response = $this->get(route('accounts.show', $otherAccount->id));
        $response->assertStatus(403); 
    }

    /**
     * Test that balance calculations are accurate.
     */
    public function test_balance_calculation_accuracy()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id, 'current_balance' => 1000.00]);

        // Create transactions
        AccountTransaction::create([
            'account_id' => $account->id,
            'transaction_type' => TransactionTypes::DEBIT,
            'amount' => 200.00,
            'created_at' => '2024-01-01',
            'balance_after_transaction' => 800.00
        ]);

        AccountTransaction::create([
            'account_id' => $account->id,
            'transaction_type' => TransactionTypes::CREDIT,
            'amount' => 400.00,
            'created_at' => '2024-01-02',
            'balance_after_transaction' => 1200.00
        ]);

        // Fetch the daily balance summary
        $response = $this->actingAs($user)
            ->getJson(route('accounts.dailyBalance', [
                'account' => $account->id,
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-02'
            ]));

        // Assert that the response contains the correct balance calculations
        $response->assertStatus(200)
            ->assertJson([
                [
                    'date' => '2024-01-01',
                    'opening_balance' => 1000.00,
                    'total_debits' => 200.00,
                    'total_credits' => 0.00,
                    'closing_balance' => 800.00
                ],
                [
                    'date' => '2024-01-02',
                    'opening_balance' => 800.00,
                    'total_debits' => 0.00,
                    'total_credits' => 400.00,
                    'closing_balance' => 1200.00
                ]
            ]);
    }

    /**
     * Test that a new user gets seeded accounts.
     * This test verifies that when a new user is created, they are automatically seeded with accounts.
     * It also checks that these accounts have initial transactions.
     */
    public function test_new_user_gets_seeded_accounts()
    {
        // Create a new user
        $newUser = User::factory()->create();

        // Generate accounts and transactions for the new user
        (new AccountService())->generateAccountsForNewUser($newUser->id);

        // Assert that the accounts table has an entry for the new user
        $this->assertDatabaseHas('accounts', [
            'user_id' => $newUser->id
        ]);

        // Fetch the first account for the new user
        $firstAccount = Account::where('user_id', $newUser->id)->first();

        // Assert that the account_transactions table has an entry for the first account
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $firstAccount->id
        ]);
    }

    /**
     * Test that the date range validation works as expected.
     * This test verifies that the API returns a 422 status code when the start date is after the end date.
     */
    public function test_date_range_validation()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('accounts.dailyBalance', [
            'account' => $this->account->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-01'
        ]));

        $response->assertStatus(422);
    }

    /**
     * Test that the daily balance includes all dates in the range.
     * This test verifies that when fetching the daily balance for a range of dates, all dates within that range are included in the response, even if there are no transactions on certain days.
     */
    public function test_daily_balance_includes_all_dates_in_range()
    {
        $this->actingAs($this->user);

        // Create a transaction to ensure there is at least one transaction within the date range
        AccountTransaction::create([
            'account_id' => $this->account->id,
            'transaction_type' => TransactionTypes::DEBIT,
            'amount' => 100.00,
            'created_at' => '2024-01-01',
            'balance_after_transaction' => 900.00
        ]);

        $response = $this->getJson(route('accounts.dailyBalance', [
            'account' => $this->account->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-03'
        ]));

        // Assert that the response includes data for all 3 days in the range, even if there are no transactions on certain days
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }
}
