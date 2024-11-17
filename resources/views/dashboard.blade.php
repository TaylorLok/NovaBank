<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    {{ __("Accounts") }}
                </div>
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                <div class="p-6 text-center">
                    <h2 class="text-2xl font-bold">Your Accounts</h2>
                    @if($accounts->isEmpty())
                        <p>You have no accounts. Please create one.</p>
                    @else
                        <table class="min-w-full mt-4 border mx-auto">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">Account Name</th>
                                    <th class="px-4 py-2">Account Number</th>
                                    <th class="px-4 py-2">Current Balance</th>
                                    <th class="px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                    <tr>
                                        <td class="border px-4 py-2">{{ $account->account_type }}</td>
                                        <td class="border px-4 py-2">{{ $account->account_number }}</td>
                                        <td class="border px-4 py-2">${{ number_format($account->current_balance, 2) }}</td>
                                        <td class="border px-4 py-2">
                                        <a href="{{ route('accounts.dailyBalance', ['accountId' => $account->id]) }}" class="text-blue-500 hover:text-blue-700">View Daily Summary</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
