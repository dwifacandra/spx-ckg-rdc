<div class="p-6">
    <div class="mb-6">
        {{-- Header Teks --}}
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Asset Transactions
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
            Track asset check-in and check-out transactions.
        </p>
    </div>

    {{-- Container utama yang disederhanakan agar lebih sesuai dengan konteks dashboard --}}
    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg mb-6 p-6">
        <div class="row-gap-8 grid grid-cols-2 md:grid-cols-4">

            {{-- Statistik 1: Total Transactions --}}
            <div class="mb-6 text-center md:mb-0 md:border-r md:border-slate-200 dark:md:border-neutral-600">
                <div
                    class="font-heading text-2xl font-bold text-blue-600 dark:text-blue-400 lg:text-3xl xl:text-4xl truncate">
                    {{ $stats["total"] }}
                </div>
                <p
                    class="text-xs font-medium uppercase tracking-widest text-gray-500 dark:text-neutral-400 lg:text-sm mt-1">
                    Total Transactions
                </p>
            </div>

            {{-- Statistik 2: Currently In Use --}}
            <div class="mb-6 text-center md:mb-0 md:border-r md:border-slate-200 dark:md:border-neutral-600">
                <div
                    class="font-heading text-2xl font-bold text-green-600 dark:text-green-400 lg:text-3xl xl:text-4xl truncate">
                    {{ $stats["active"] }}
                </div>
                <p
                    class="text-xs font-medium uppercase tracking-widest text-gray-500 dark:text-neutral-400 lg:text-sm mt-1">
                    Currently In Use
                </p>
            </div>

            {{-- Statistik 3: Completed --}}
            <div class="mb-6 text-center md:mb-0 md:border-r md:border-slate-200 dark:md:border-neutral-600">
                <div
                    class="font-heading text-2xl font-bold text-gray-700 dark:text-gray-300 lg:text-3xl xl:text-4xl truncate">
                    {{ $stats["complete"] }}
                </div>
                <p
                    class="text-xs font-medium uppercase tracking-widest text-gray-500 dark:text-neutral-400 lg:text-sm mt-1">
                    Completed
                </p>
            </div>

            {{-- Statistik 4: Overdue --}}
            <div class="mb-6 text-center md:mb-0">
                <div
                    class="font-heading text-2xl font-bold text-red-600 dark:text-red-400 lg:text-3xl xl:text-4xl truncate">
                    {{ $stats["overdue"] }}
                </div>
                <p
                    class="text-xs font-medium uppercase tracking-widest text-gray-500 dark:text-neutral-400 lg:text-sm mt-1">
                    Overdue
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <flux:input icon="magnifying-glass" placeholder="Search by asset code, item, brand, or user..."
                        wire:model.live.debounce.300ms="search" />
                </div>

                <div>
                    <flux:select wire:model.live="statusFilter" placeholder="Choose Status">
                        <flux:select.option value="">All Status</flux:select.option>
                        <flux:select.option value="in use">In Use</flux:select.option>
                        <flux:select.option value="complete">Complete</flux:select.option>
                        <flux:select.option value="overtime">Overdue</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex items-end">
                    <flux:button wire:click="resetFilters">Clear Filters</flux:button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto" wire.poll>
            {{-- DIVIDER TABLE: Menggunakan dark:divide-neutral-700 --}}
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">

                {{-- TABLE HEAD: Menggunakan dark:bg-neutral-700 --}}
                <thead class="bg-gray-50 dark:bg-neutral-700">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 uppercase tracking-wider">
                            <button wire:click="sortBy('ops_id')"
                                class="group inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white">
                                <span>OPS Information</span>
                                @if ($sortField === 'ops_id')
                                <span class="text-gray-500 dark:text-neutral-400">
                                    {{ $sortDirection === "asc" ? "↑" : "↓" }}
                                </span>
                                @endif
                            </button>
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 tracking-wider">
                            Asset
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 tracking-wider">
                            Check Out
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 tracking-wider">
                            <button wire:click="sortBy('check_in')"
                                class="group inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white">
                                <span>Check In</span>
                                @if ($sortField === 'check_in')
                                <span class="text-gray-500 dark:text-neutral-400">
                                    {{ $sortDirection === "asc" ? "↑" : "↓" }}
                                </span>
                                @endif
                            </button>
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 tracking-wider">
                            Duration
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 tracking-wider">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 tracking-wider">
                            Issue
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 tracking-wider">
                            User
                        </th>
                    </tr>
                </thead>

                {{-- TABLE BODY --}}
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse ($transactions as $transaction)
                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <div class="text-sm  text-gray-900 dark:text-white">
                                    {{ $transaction->ops_id }}
                                </div>
                                <div>
                                    <span
                                        class="font-mono text-sm  px-2 py-0.5 rounded text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700">
                                        {{ $transaction->ops_profile->staff_name }}
                                    </span>
                                </div>

                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="text-sm  text-gray-900 dark:text-white">
                                    {{ $transaction->asset->code }}
                                </div>
                                <div>
                                    <span
                                        class="font-mono text-sm  px-2 py-0.5 rounded text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700">
                                        {{ $transaction->asset->serial_number }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            @if ($transaction->check_out)
                            {{ $transaction->check_out->format('d/m/Y H:i:s') }}
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            @if ($transaction->check_in)
                            {{ $transaction->check_in->format('d/m/Y H:i:s') }}
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <div class="flex items-center space-x-2">
                                <x-icon name="clock" class="w-4 h-4 text-gray-500 dark:text-gray-400" />

                                <span>{{ $transaction->getDurationInHours() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($transaction->status === 'complete')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                Complete
                            </span>
                            @elseif ($transaction->status === 'in use')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                                In Use
                            </span>
                            @elseif ($transaction->status === 'overtime')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                Overdue
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $transaction->remarks }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $transaction->user->email }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-neutral-400">
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
        {{-- PAGINATION CONTAINER: Menggunakan dark:bg-neutral-800 dan dark:border-neutral-700 --}}
        <div
            class="bg-white dark:bg-neutral-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-neutral-700 sm:px-6">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
