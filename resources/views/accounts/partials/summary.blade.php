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
            @forelse($summary as $balance)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($balance['date'])->format('d-M-y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        R {{ number_format($balance['opening_balance'], 2) }}
                    </td>
                    <td class="px-6 py-4 text-right text-red-600">
                        @if($balance['total_debits'] > 0)
                            -R {{ number_format($balance['total_debits'], 2) }}
                        @else
                            R 0.00
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-green-600">
                        @if($balance['total_credits'] > 0)
                            +R {{ number_format($balance['total_credits'], 2) }}
                        @else
                            R 0.00
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right font-medium">
                        R {{ number_format($balance['closing_balance'], 2) }}
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

