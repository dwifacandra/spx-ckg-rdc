<div>
    <div class="bg-white dark:bg-zinc-800 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-2xl font-semibold mb-4">Asset Items</h2>

            @if (session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session("message") }}
            </div>
            @endif

            <div class="mb-4 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                <div class="flex-1">
                    <flux:input icon="magnifying-glass" placeholder="Search Assets" wire:model.live="search" />
                </div>
                <flux:button.group>
                    <flux:button variant="outline" icon="arrow-down-tray" wire:click="downloadTemplate">
                        Download Template
                    </flux:button>
                    <flux:modal.trigger name="import-modal">
                        <flux:button variant="outline" icon="arrow-up-tray">
                            Import
                        </flux:button>
                    </flux:modal.trigger>
                </flux:button.group>
            </div>

            <div class="overflow-x-auto border-t border-neutral-200 dark:border-neutral-700" wire:poll>
                <table class="min-w-full">
                    <thead class="bg-neutral-50 dark:bg-neutral-700">
                        <tr>
                            @foreach(['Item', 'Brand', 'Code', 'Type', 'Tag', 'Serial Number', 'Condition', 'Status',
                            'Remarks'] as $header)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                {{ $header }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($assets as $asset)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $asset->item }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $asset->brand }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $asset->code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $asset->type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $asset->tag }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $asset->serial_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $asset->condition === 'good' ? 'bg-green-100 text-green-800 dark:bg-green-700/30 dark:text-green-300' :
                                       ($asset->condition === 'damaged' ? 'bg-red-100 text-red-800 dark:bg-red-700/30 dark:text-red-300' :
                                       'bg-yellow-100 text-yellow-800 dark:bg-yellow-700/30 dark:text-yellow-300') }}">
                                    {{ ucfirst($asset->condition) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $asset->status === 'in use' ? 'bg-blue-100 text-blue-800 dark:bg-blue-700/30 dark:text-blue-300' :
                                       ($asset->status === 'standby' ? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700/30 dark:text-neutral-300' :
                                       ($asset->status === 'lost' ? 'bg-red-100 text-red-800 dark:bg-red-700/30 dark:text-red-300' :
                                       'bg-yellow-100 text-yellow-800 dark:bg-yellow-700/30 dark:text-yellow-300')) }}">
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $asset->remarks }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12"
                                class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-300 text-center">
                                Tidak ada asset yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </div>
    </div>
    <livewire:assets.import />
</div>
