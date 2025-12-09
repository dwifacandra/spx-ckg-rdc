<flux:modal name="import-modal" :show="$show">
    <form wire:submit.prevent="import">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Import Assets</flux:heading>
                <flux:subheading>Select a CSV file to import.</flux:subheading>
            </div>
            <div>
                <flux:input type="file" wire:model="file" />
                @error('file')
                <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>
            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary"
                    >Import</flux:button
                >
            </div>
        </div>
    </form>
</flux:modal>
