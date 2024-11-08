<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-600 flex items-center">
                    <span>←</span>
                    <span class="ml-2">Back to Dashboard</span>
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-semibold mb-6">
                    Daily Movement Summary for <span class="text-blue-500">{{ $account->account_type }} Account </span>
                </h2>

                <form id="dateRangeForm" method="GET" class="mb-6 flex gap-4 items-end">
                    @csrf
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input 
                            type="date" 
                            id="start_date"
                            name="start_date" 
                            value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            required
                        >
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input 
                            type="date" 
                            id="end_date"
                            name="end_date" 
                            value="{{ request('end_date', now()->format('Y-m-d')) }}" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            required
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-gray-200 text-black rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 hover:bg-green-500"
                    >
                        Filter
                    </button>
                </form>

                <div id="summaryTable" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Opening Balance</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Debits</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Credits</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary as $item)
                                <tr class="bg-white">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">R {{ number_format($item->opening_balance, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-red-600">
                                        {{ $item->total_debits > 0 ? '-R ' . number_format($item->total_debits, 2) : 'R 0.00' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-green-600">
                                        {{ $item->total_credits > 0 ? '+R ' . number_format($item->total_credits, 2) : 'R 0.00' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium">R {{ number_format($item->closing_balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No transactions found for the selected date range</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $summary->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('dateRangeForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be after end date');
                return;
            }

            const url = `{{ route('accounts.dailyBalance', ['accountId' => $account->id]) }}?start_date=${startDate}&end_date=${endDate}`;
            
            // Show loading state
            document.querySelector('tbody').innerHTML = '<tr><td colspan="5" class="text-center py-4">Loading...</td></tr>';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '';

                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No transactions found</td></tr>';
                    return;
                }

                data.data.forEach(item => {
                    const date = new Date(item.date).toLocaleDateString('en-ZA', { 
                        day: '2-digit', 
                        month: 'short', 
                        year: 'numeric' 
                    });

                    tbody.insertAdjacentHTML('beforeend', `
                        <tr class="bg-white">
                            <td class="px-6 py-4 whitespace-nowrap">${date}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">R ${Number(item.opening_balance).toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-red-600">
                                ${item.total_debits > 0 ? '-R ' + Number(item.total_debits).toFixed(2) : 'R 0.00'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-green-600">
                                ${item.total_credits > 0 ? '+R ' + Number(item.total_credits).toFixed(2) : 'R 0.00'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium">R ${Number(item.closing_balance).toFixed(2)}</td>
                        </tr>
                    `);
                });

                // Update pagination if it exists
                const paginationContainer = document.querySelector('.mt-4');
                if (paginationContainer && data.links) {
                    paginationContainer.innerHTML = data.links;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.querySelector('tbody').innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-red-500">
                            An error occurred while fetching the data
                        </td>
                    </tr>
                `;
            });
        });
    </script>
    @endpush
</x-app-layout>