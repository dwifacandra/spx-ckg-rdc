<div class="p-6">
    <div class="mb-6">
        {{-- Header Teks sudah Dark Mode --}}
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            📤 Asset Check Out
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
            Enter OPS ID and Asset Code to record Check Out.
        </p>
    </div>

    {{-- Form Input Container --}}
    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg p-6 mb-8">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:field>
                        {{-- Label menggunakan Dark Mode Teks --}}
                        <flux:label for="opsId" class="dark:text-white">OPS ID</flux:label>
                        {{-- Diasumsikan flux:input sudah menangani Dark Mode styling untuk inputnya --}}
                        <flux:input id="opsId" placeholder="Enter or Scan OPS ID" wire:model.blur="opsId" autofocus
                            autocomplete="off" x-data
                            x-init="$wire.on('focus-ops-id', () => { $el.focus(); $el.select(); })" />
                    </flux:field>
                </div>
                <div>
                    <flux:field>
                        {{-- Label menggunakan Dark Mode Teks --}}
                        <flux:label for="assetCode" class="dark:text-white">ASSET CODE</flux:label>
                        {{-- Diasumsikan flux:input sudah menangani Dark Mode styling untuk inputnya --}}
                        <flux:input id="assetCode" placeholder="Enter or Scan Asset Code" wire:model.blur="assetCode"
                            autocomplete="off" />
                    </flux:field>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        @php
        $statusClass = match ($statusMessage) {
        'Waiting for scan' =>
        'bg-white dark:bg-neutral-800 border-gray-200 dark:border-neutral-700 text-gray-700 dark:text-neutral-300',
        'Check Out' => 'bg-green-500 border-green-500 text-white',
        default => 'bg-red-500 border-red-500 text-white',
        };
        @endphp

        {{-- Status Card (Disesuaikan untuk Dark Mode) --}}
        <div class="p-6 rounded-lg border h-40 text-center flex flex-col justify-center items-center {{
                $statusClass
            }}">
            <p class="text-3xl font-extrabold break-words">
                {{ $statusMessage }}
            </p>
            @if ($statusMessage === 'Failed' && $failureReason)
            <p class="mt-3 text-lg font-medium pt-2">
                {{ $failureReason }}
            </p>
            @endif
        </div>

        {{-- OPS Information Card (Menggunakan neutral-800) --}}
        <div
            class="bg-white dark:bg-neutral-800 rounded-lg p-6 border border-gray-200 dark:border-neutral-700 h-40 overflow-auto">
            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">
                👤 OPS Information
            </h3>
            <dl class="text-sm text-gray-700 dark:text-neutral-300 space-y-1">
                <div class="flex">
                    <dt class="w-40 font-medium text-gray-900 dark:text-white">
                        OPS ID
                    </dt>
                    <dd class="flex-1 text-gray-600 dark:text-neutral-400">
                        : {{ $selectedEmployee->ops_id ?? null }}
                    </dd>
                </div>
                <div class="flex">
                    <dt class="w-40 font-medium text-gray-900 dark:text-white">
                        Staff Name
                    </dt>
                    <dd class="flex-1 text-gray-600 dark:text-neutral-400">: {{ $selectedEmployee->staff_name ?? null }}
                    </dd>
                </div>
                <div class="flex">
                    <dt class="w-40 font-medium text-gray-900 dark:text-white">
                        Contract Type
                    </dt>
                    <dd class="flex-1 text-gray-600 dark:text-neutral-400">: {{ $selectedEmployee->contract_type ?? null }}</dd>
                </div>
            </dl>
        </div>

        {{-- Asset Information Card (Menggunakan neutral-800) --}}
        <div
            class="bg-white dark:bg-neutral-800 rounded-lg p-6 border border-gray-200 dark:border-neutral-700 h-40 overflow-auto">
            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">
                🏷️ Asset Information
            </h3>
            <dl class="text-sm text-gray-700 dark:text-neutral-300 space-y-1">
                <div class="flex">
                    <dt class="w-40 font-medium text-gray-900 dark:text-white">
                        Asset Item
                    </dt>
                    <dd class="flex-1 text-gray-600 dark:text-neutral-400">
                        : {{ $selectedAsset->item ?? null }}
                    </dd>
                </div>
                <div class="flex">
                    <dt class="w-40 font-medium text-gray-900 dark:text-white">
                        Tag
                    </dt>
                    <dd class="flex-1 text-gray-600 dark:text-neutral-400">
                        : {{ $selectedAsset->tag ?? null }}
                    </dd>
                </div>
                <div class="flex">
                    <dt class="w-40 font-medium text-gray-900 dark:text-white">
                        Serial Number
                    </dt>
                    <dd class="flex-1 text-gray-600 dark:text-neutral-400">
                        : {{ $selectedAsset->serial_number ?? null }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Recent Transactions Table Container --}}
    <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg overflow-hidden">
        {{-- Table Header Bar --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Recent Check Out 🕒
            </h2>
        </div>
        <div class="overflow-x-auto">
            {{-- Table Divider --}}
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                {{-- Table Head --}}
                <thead class="bg-gray-50 dark:bg-neutral-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 uppercase tracking-wider">
                            OPS ID
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 uppercase tracking-wider">
                            Asset Code
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 uppercase tracking-wider">
                            Duration
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-300 uppercase tracking-wider">
                            Transaction Datetime
                        </th>
                    </tr>
                </thead>
                {{-- Table Body --}}
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse($recentTransactions as $transaction)
                    {{-- Table Row Hover --}}
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
                            <div class="flex items-center space-x-2">
                                <x-icon name="clock" class="w-4 h-4 text-gray-500 dark:text-gray-400" />

                                <span>{{ $transaction->getDurationInHours() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{-- Badge Status (Menggunakan skema warna Dark Mode yang sudah ada) --}}
                            <span
                                class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-neutral-400">
                            {{ $transaction->created_at->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-neutral-400">
                            No recent check-out transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('start-reset-timer', ({
            seconds = 10
        }) => {
            console.log(`Starting reset timer for ${seconds} seconds...`);
            setTimeout(() => {
                @this.dispatch('resetFormNow');

                setTimeout(() => {
                    document.getElementById('opsId').focus();
                }, 100);
            }, seconds * 1000);
        });

        document.getElementById('opsId').focus();
    });
</script>
