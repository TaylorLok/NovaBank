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
    protected AccountService $accountService;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialize AccountService instance
        $this->accountService = app(AccountService::class);
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
     * Test account creation and transaction generation for a new user.
     *
     * @return void
     */
    public function test_account_and_transaction_creation_for_new_user()
    {
        // Step 1: Create a new user
        $user = User::factory()->create();

        // Step 2: Generate accounts and transactions for this user
        $this->accountService->generateAccountsForNewUser($user->id);

        // Step 3: Fetch the accounts and transactions from the database and assert they are created correctly
        $accounts = Account::where('user_id', $user->id)->get();

        $this->assertNotEmpty($accounts, 'Accounts should be created for the user');
        $this->assertGreaterThanOrEqual(1, $accounts->count(), 'At least one account should be created for the user');

        foreach ($accounts as $account) {
            // Ensure transactions are generated for each account
            $transactions = AccountTransaction::where('account_id', $account->id)->get();
            $this->assertNotEmpty($transactions, "Transactions should be created for account ID: {$account->id}");

            // Check that each transaction has an amount and a transaction type (debit or credit)
            foreach ($transactions as $transaction) {
                $this->assertIsNumeric($transaction->amount, 'Transaction amount should be numeric');
                $this->assertContains(strtoupper($transaction->transaction_type), ['DEBIT', 'CREDIT'], 'Transaction type should be DEBIT or CREDIT');
            }
        }
    }

    /**
     * Test that a user cannot access accounts they do not own.
     *
     * @return void
     */
    public function test_user_cannot_access_other_users_account()
    {
        // Step 1: Create two users
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Step 2: Create an account for User A
        $accountForUserA = Account::factory()->create(['user_id' => $userA->id]);

        // Step 3: Authenticate as User B
        $this->actingAs($userB);

        // Step 4: Attempt to access User A's account
        $response = $this->get(route('accounts.show', ['accountId' => $accountForUserA->id]));

        // Step 5: Assert forbidden status (403)
        $response->assertForbidden();
    }

    /**
     * Test that a user can access their own account.
     *
     * @return void
     */
    public function test_user_can_access_their_own_account()
    {
        // Step 1: Create a user and an account for that user
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);

        // Step 2: Authenticate as this user
        $this->actingAs($user);

        // Step 3: Attempt to access their own account
        $response = $this->get(route('accounts.show', ['accountId' => $account->id]));

        // Step 4: Assert successful access (200)
        $response->assertOk();
        $response->assertViewIs('accounts.show'); // Optionally assert the correct view is returned
    }
    
    /**
     * Test to ensure that a user can only see their own accounts.
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
}
