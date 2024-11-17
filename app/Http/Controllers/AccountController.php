<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Http\Requests\DailyBalanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

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
            // Get account and verify ownership in one query
            $account = $this->accountService->getAccountForUser(auth()->id(), $accountId);
            
            if (!$account) {
                return $request->wantsJson()
                    ? response()->json(['error' => 'Account not found'], 404)
                    : redirect()->route('dashboard')->with('error', 'Account not found');
            }
    
            // Get validated dates with defaults
            $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));
    
            // Fetch daily balance summary
            $collection = $this->accountService->getDailyBalanceSummary(
                auth()->id(),
                $accountId,
                $startDate,
                $endDate
            );
    
            // Efficient pagination
            $page = $request->input('page', 1);
            $perPage = 10;
            $items = $collection->forPage($page, $perPage);
            
            $paginator = new LengthAwarePaginator(
                $items,
                $collection->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->except('page')]
            );
    
            if ($request->wantsJson()) {
                return response()->json($paginator);
            }
    
            return view('accounts.daily-balance', [
                'account' => $account,
                'summary' => $paginator 
            ]);
    
        } catch (Exception $e) {
            // Enhanced error logging
            \Log::error('Daily balance error', [
                'message' => $e->getMessage(),
                'account_id' => $accountId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = config('app.debug') 
                ? $e->getMessage() 
                : 'Failed to fetch daily balance data';
    
            return $request->wantsJson()
                ? response()->json(['error' => $errorMessage], 500)
                : redirect()->route('dashboard')->with('error', $errorMessage);
        }
    }
}
