<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Http\Requests\DailyBalanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class AccountController extends Controller
{
    public function __construct(protected AccountService $accountService) 
    {
    }

    /**
     * Display a listing of the user's accounts.
     */
    public function index()
    {
        try 
        {
            $accounts = $this->accountService->getAllUserAccounts(auth()->id());
            return view('accounts.index', compact('accounts'));
        } 
        catch (Exception $e) {

            \Log::error('Error fetching accounts: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to fetch accounts at this time.');
        }
    }

    /**
     * Display a specific account for the authenticated user.
     */
    public function show(int $accountId)
    {
        try {
            $account = $this->accountService->getAccountForUser(auth()->id(), $accountId);

            if (!$account) {
                return redirect()->route('accounts.index')->with('error', 'Account not found.');
            }

            return view('accounts.show', compact('account'));
        } catch (Exception $e) {
            \Log::error('Error fetching account: ' . $e->getMessage());
            return redirect()->route('accounts.index')->with('error', 'Unable to fetch the account at this time.');
        }
    }

    /**
     * Display the daily balance summary for the given account.
     */
    public function dailyBalance(DailyBalanceRequest $request, int $accountId)
    {
        try {
            $summary = $this->accountService->getDailyBalanceSummary(auth()->id(), $accountId, $request->start_date, $request->end_date);

            if ($request->wantsJson()) {
                return response()->json($summary);
            }

            return view('accounts.daily-balance', [
                'summary' => $summary,
                'account' => $this->accountService->getAccountForUser(auth()->id(), $accountId)
            ]);
        } 
        catch (Exception $e) {
            \Log::error('Error fetching daily balance summary: ' . $e->getMessage());
            return redirect()->route('accounts.index')->with('error', 'Unable to fetch the daily balance summary at this time.');
        }
    }
}
