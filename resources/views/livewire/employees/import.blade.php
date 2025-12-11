<flux:modal name="import-modal" :show="$show">
    <form wire:submit.prevent="import">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Import Employees</flux:heading>
                <flux:subheading>Select a CSV file to import.</flux:subheading>
            </div>

            @if (session()->has('import_status') && !$isImporting)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded dark:bg-green-900/30 dark:border-green-800 dark:text-green-300"
                role="alert">
                {!! session('import_status') !!} @if ($failedCount > 0 &&
                $failedFileName)
                <div class="mt-2">
                    <flux:button size="sm" variant="outline" icon="arrow-down-tray" wire:click="downloadFailedReport"
                        class="bg-white hover:bg-gray-100 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:text-white dark:border-zinc-600">
                        Download Failed Report ({{ $failedCount }} records)
                    </flux:button>
                </div>
                @endif
            </div>
            @php session()->forget('import_status') @endphp

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>

            @elseif ($isImporting)
            <div class="space-y-4" wire:poll.1000ms="checkProgress">
                <div class="w-full bg-neutral-200 dark:bg-zinc-700 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                        style="width: {{ $this->progressWidth }}"></div>
                </div>

                <div class="text-center text-sm text-gray-600 dark:text-neutral-400">
                    Importing... {{ round($this->progress) }}%
                </div>
            </div>

            @else
            <div>
                <flux:input type="file" wire:model="file" required />
                @error('file')
                <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>
            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Import</flux:button>
            </div>
            @endif
        </div>
    </form>
</flux:modal>
