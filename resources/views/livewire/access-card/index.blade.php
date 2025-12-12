<div>
    <div class="bg-white dark:bg-zinc-800 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-2xl font-semibold mb-4">Access Card</h2>
            <div class="mb-4 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                <div class="flex-1">
                    <flux:input icon="magnifying-glass" placeholder="Search Access Card" wire:model.live="search" />
                </div>
            </div>
            <div class="overflow-x-auto border-t border-neutral-200 dark:border-neutral-700" wire:poll>
                <div class="p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                        @forelse($cards as $card)
                        <div
                            class="flex flex-col items-center group cursor-pointer border border-transparent hover:border-neutral-300 dark:hover:border-neutral-600 rounded-lg transition duration-300">
                            @php
                            $statusClass = match ($card->status) {
                            'standby' => 'bg-green-600 dark:bg-green-700',
                            'in use' => 'bg-blue-600 dark:bg-blue-700',
                            'lost' => 'bg-red-600 dark:bg-red-700',
                            'maintenance' => 'bg-neutral-500 dark:bg-neutral-600',
                            default => 'bg-yellow-500 dark:bg-yellow-600',
                            };

                            $statusText = ucfirst($card->status);
                            @endphp

                            <div class="w-full aspect-[2/3] rounded-lg shadow-md overflow-hidden
                            {{ $statusClass }}
                            flex flex-col items-center justify-center p-2">
                                <span class="text-white text-2xl text-wrap font-extrabold select-none text-center">
                                    {{ $statusText }}
                                </span>
                                @if (!empty($card->remarks))
                                <span
                                    class="block text-xs text-center text-white mt-0.5 whitespace-normal break-words px-1 line-clamp-2">
                                    ({{ $card->remarks }})
                                </span>
                                @endif
                            </div>
                            <div class="mt-2 text-center w-full">
                                <span class="block text-sm font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ $card->card_number }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div
                            class="col-span-full py-8 text-center text-neutral-500 dark:text-neutral-400 border border-dashed rounded-lg">
                            Tidak ada Access Card yang ditemukan.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mt-4">
                {{ $cards->links() }}
            </div>
        </div>
    </div>
</div>
