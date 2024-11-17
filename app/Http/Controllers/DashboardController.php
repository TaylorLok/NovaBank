<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with the user's accounts.
     */
    public function index()
    {
        $user = Auth::user();
        $accounts = Account::where('user_id', $user->id)->get(); 

        return view('dashboard', compact('accounts')); 
    }
}
