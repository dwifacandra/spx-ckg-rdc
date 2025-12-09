<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Asset Transactions
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Track asset check-in and check-out transactions.
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg"
        >
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center"
                        >
                            <span class="text-white font-semibold">{{
                                $stats["total"]
                            }}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            Total Transactions
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg"
        >
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"
                        >
                            <span class="text-white font-semibold">{{
                                $stats["active"]
                            }}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            Currently In Use
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg"
        >
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center"
                        >
                            <span class="text-white font-semibold">{{
                                $stats["complete"]
                            }}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            Completed
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg"
        >
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center"
                        >
                            <span class="text-white font-semibold">{{
                                $stats["overdue"]
                            }}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            Overdue
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label
                        for="search"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Search
                    </label>
                    <input
                        type="text"
                        id="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by asset code, item, brand, or user..."
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <label
                        for="status"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Status
                    </label>
                    <select
                        id="status"
                        wire:model.live="statusFilter"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">All Status</option>
                        <option value="in use">In Use</option>
                        <option value="complete">Complete</option>
                        <option value="overtime">Overdue</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button
                        wire:click="resetFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
            >
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            <button
                                wire:click="sortBy('ops_id')"
                                class="group inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white"
                            >
                                <span>Transaction ID</span>
                                @if ($sortField === 'ops_id')
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $sortDirection === "asc" ? "↑" : "↓" }}
                                </span>
                                @endif
                            </button>
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Asset
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            <button
                                wire:click="sortBy('check_in')"
                                class="group inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white"
                            >
                                <span>Check In</span>
                                @if ($sortField === 'check_in')
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $sortDirection === "asc" ? "↑" : "↓" }}
                                </span>
                                @endif
                            </button>
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Check Out
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            User
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                            Duration
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                >
                    @forelse ($transactions as $transaction)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ $transaction->ops_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ $transaction->asset->item }}
                                    </div>
                                    <div
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ $transaction->asset->brand }} -
                                        {{ $transaction->asset->code }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                        >
                            {{ $transaction->check_in->format('M d, Y H:i') }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                        >
                            @if ($transaction->check_out)
                            {{ $transaction->check_out->format('M d, Y H:i') }}
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                        >
                            {{ $transaction->created_by }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($transaction->status === 'in use')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
                            >
                                In Use
                            </span>
                            @elseif ($transaction->status === 'complete')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            >
                                Complete
                            </span>
                            @elseif ($transaction->status === 'overtime')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300"
                            >
                                Overdue
                            </span>
                            @endif
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                        >
                            {{ $transaction->getDurationInDays() }} days
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td
                            colspan="7"
                            class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400"
                        >
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($transactions->hasPages())
        <div
            class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6"
        >
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
