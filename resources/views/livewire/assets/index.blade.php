<div>
    <div
        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
    >
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-2xl font-semibold mb-4">Asset Items</h2>

            @if (session()->has('message'))
            <div
                class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded"
            >
                {{ session("message") }}
            </div>
            @endif

            <!-- Search Input and Actions -->
            <div
                class="mb-4 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center"
            >
                <div class="flex-1">
                    <flux:input
                        icon="magnifying-glass"
                        placeholder="Search Assets"
                        wire:model.live="search"
                    />
                </div>
                <flux:button.group>
                    <flux:button
                        variant="outline"
                        icon="arrow-down-tray"
                        wire:click="downloadTemplate"
                    >
                        Download Template
                    </flux:button>
                    <flux:modal.trigger name="import-modal">
                        <flux:button variant="outline" icon="arrow-up-tray">
                            Import
                        </flux:button>
                    </flux:modal.trigger>
                </flux:button.group>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800" wire:poll>
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Item
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Brand
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Code
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Type
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Tag
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Serial Number
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Condition
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                            >
                                Remarks
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        @forelse($assets as $asset)
                        <tr>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                {{ $asset->item }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                {{ $asset->brand }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                {{ $asset->code }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                {{ $asset->type }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                {{ $asset->tag }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                {{ $asset->serial_number }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $asset->condition === 'good' ? 'bg-green-100 text-green-800' : ($asset->condition === 'damaged' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}"
                                >
                                    {{ ucfirst($asset->condition) }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $asset->status === 'in use' ? 'bg-blue-100 text-blue-800' : ($asset->status === 'standby' ? 'bg-gray-100 text-gray-800' : ($asset->status === 'lost' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}"
                                >
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"
                            >
                                {{ $asset->remarks }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td
                                colspan="12"
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center"
                            >
                                Tidak ada asset yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </div>
    </div>
    <livewire:assets.import />
</div>
