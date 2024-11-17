@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-600">
            ← Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-semibold">{{ $account->account_type }}</h2>
                <p class="text-gray-600">{{ $account->account_number }}</p>
                <p class="text-gray-500">{{ $account->account_type }}</p>
            </div>
            
            <div class="flex gap-4">
                <form action="{{ route('accounts.show', $account->id) }}" method="GET" class="flex gap-4">
                    <div>
                        <label for="start_date" class="block text-sm text-gray-600">Start Date</label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ request('start_date', date('Y-m-d', strtotime('-30 days'))) }}"
                               class="border rounded px-3 py-1">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm text-gray-600">End Date</label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ request('end_date', date('Y-m-d')) }}"
                               class="border rounded px-3 py-1">
                    </div>
                    
                    <div class="self-end">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Opening Balance</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Debits</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Credits</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyBalances as $balance)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($balance->date)->format('d-M-y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                R {{ number_format($balance->opening_balance, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-red-600">
                                @if($balance->total_debits > 0)
                                    -R {{ number_format($balance->total_debits, 2) }}
                                @else
                                    R 0.00
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-green-600">
                                @if($balance->total_credits > 0)
                                    +R {{ number_format($balance->total_credits, 2) }}
                                @else
                                    R 0.00
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-medium">
                                R {{ number_format($balance->closing_balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No transactions found for the selected date range
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
