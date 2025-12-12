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

    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg mb-6" wire:poll>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 divide-x divide-slate-200 dark:divide-neutral-700">
            <div class="p-4 text-center">
                <div class="font-heading text-5xl font-extrabold text-gray-600 dark:text-gray-400 truncate">
                    {{ $stats["total"]["total"] }}
                </div>
                <p class="flex flex-col text-gray-500 dark:text-neutral-400 mt-2 mb-3">
                    <span class="text-sm tracking-widest font-medium uppercase">TOTAL ASSETS</span>
                    <span class="italic font-normal text-xs">(Total Keseluruhan Asset)</span>
                </p>
                <div
                    class="text-sm text-gray-700 dark:text-neutral-300 font-semibold flex justify-around p-2 bg-gray-50/70 dark:bg-gray-900/50 rounded">
                    <span>PDA: <span class="font-bold">{{ $stats["total"]["pda"] }}</span></span>
                    <span class="text-gray-700 dark:text-gray-400">|</span>
                    <span>HT: <span class="font-bold">{{ $stats["total"]["ht"] }}</span></span>
                </div>
            </div>
            <div class="p-4 text-center">
                <div class="font-heading text-5xl font-extrabold text-orange-600 dark:text-orange-400 truncate">
                    {{ $stats["active"]["total"] }}
                </div>
                <p class="flex flex-col text-orange-700 dark:text-orange-300 mt-2 mb-3">
                    <span class="text-sm tracking-widest font-medium uppercase">CURRENTLY IN USE</span>
                    <span class="italic font-normal text-xs">(Asset sedang dipinjam)</span>
                </p>
                <div
                    class="text-sm text-gray-700 dark:text-neutral-300 font-semibold flex justify-around p-2 bg-orange-100/70 dark:bg-orange-900/50 rounded">
                    <span>PDA: <span class="font-bold">{{ $stats["active"]["pda"] }}</span></span>
                    <span class="text-orange-700 dark:text-orange-400">|</span>
                    <span>HT: <span class="font-bold">{{ $stats["active"]["ht"] }}</span></span>
                </div>
            </div>
            <div class="p-4 text-center">
                <div class="font-heading text-5xl font-extrabold text-green-700 dark:text-green-300 truncate">
                    {{ $stats["complete"]["total"] }}
                </div>
                <p class="flex flex-col text-green-700 dark:text-green-400 mt-2 mb-3">
                    <span class="text-sm tracking-widest font-medium uppercase">COMPLETED</span>
                    <span class="italic font-normal text-xs">(Asset sudah dikembalikan)</span>
                </p>
                <div
                    class="text-sm text-gray-700 dark:text-neutral-300 font-semibold flex justify-around p-2 bg-green-100/70 dark:bg-green-900/50 rounded">
                    <span>PDA: <span class="font-bold">{{ $stats["complete"]["pda"] }}</span></span>
                    <span class="text-green-700 dark:text-green-400">|</span>
                    <span>HT: <span class="font-bold">{{ $stats["complete"]["ht"] }}</span></span>
                </div>
            </div>
            <div class="p-4 text-center">
                <div class="font-heading text-5xl font-extrabold text-red-600 dark:text-red-400 truncate">
                    {{ $stats["overdue"]["total"] }}
                </div>
                <p class="flex flex-col text-red-700 dark:text-red-300 mt-2 mb-3">
                    <span class="text-sm tracking-widest font-medium uppercase">OVERDUE</span>
                    <span class="italic font-normal text-xs">(Transaksi lebih dari 9 jam)</span>
                </p>
                <div
                    class="text-sm text-gray-700 dark:text-neutral-300 font-semibold flex justify-around p-2 bg-red-100/70 dark:bg-red-900/50 rounded">
                    <span>PDA: <span class="font-bold">{{ $stats["overdue"]["pda"] }}</span></span>
                    <span class="text-red-700 dark:text-red-400">|</span>
                    <span>HT: <span class="font-bold">{{ $stats["overdue"]["ht"] }}</span></span>
                </div>
            </div>

        </div>
    </div>

    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="col-span-2">
                    <flux:input icon="magnifying-glass" placeholder="Search by ops id, code, item, tag, sn, or user..."
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
                <div>
                    <flux:input.group>
                        <flux:input.group.prefix>Check Out</flux:input.group.prefix>
                        <flux:input type="date" wire:model.live="checkOutDateFilter" id="check_out_date" />
                    </flux:input.group>
                </div>
                <div class="pl-4">
                    <flux:input.group>
                        <flux:input.group.prefix>Check In</flux:input.group.prefix>
                        <flux:input type="date" wire:model.live="checkInDateFilter" id="check_in_date" />
                    </flux:input.group>
                </div>
                <div class="pl-4 flex items-end">
                    <flux:button wire:click="resetFilters">Clear Filters</flux:button>
                </div>

            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto" wire.poll>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
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
                            {{ $transaction->user->name }}
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
        <div
            class="bg-white dark:bg-neutral-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-neutral-700 sm:px-6">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
