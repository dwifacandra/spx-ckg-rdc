<div>
    <h2 class="text-2xl font-bold mb-6">Asset Finder & Transaction Tracker</h2>

    @if (session()->has('message'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        {{ session('message') }}
    </div>
    @endif

    {{-- FORMULIR INPUT (FULL WIDTH) --}}
    <div class="bg-white shadow-lg rounded-lg px-6 py-8 mb-8 w-full" id="crud-form">
        <form wire:submit.prevent="save">
            <h3 class="text-xl font-semibold mb-6 border-b pb-2">
                {{ $is_editing ? 'Edit Status Pencarian: ' . $asset_id : 'Tambah Asset Baru' }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Kolom Kiri Form --}}
                <div>
                    <div class="mb-4">
                        <label for="asset_id" class="block text-gray-700 text-sm font-bold mb-2">Asset Code</label>
                        <input type="text" id="asset_id" wire:model.defer="asset_id"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            {{ $is_editing ? 'disabled' : '' }} placeholder="Masukkan Kode Asset">

                        @if ($is_editing)
                        <p class="text-xs text-gray-500 mt-1">Kode Asset tidak bisa diubah.</p>
                        @endif
                        @error('asset_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                        <select id="status" wire:model.defer="status"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Kolom Kanan Form --}}
                <div>
                    <div class="mb-4">
                        <label for="remarks" class="block text-gray-700 text-sm font-bold mb-2">Remarks</label>
                        <textarea id="remarks" wire:model.defer="remarks"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-32"
                            placeholder="Catatan tentang status pencarian."></textarea>
                        @error('remarks') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-start mt-4 border-t pt-4">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 mr-3">
                    {{ $is_editing ? 'Update Status' : 'Simpan' }}
                </button>
                @if ($is_editing)
                <button wire:click="resetForm" type="button"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150">
                    Batal Edit / Tambah Baru
                </button>
                @endif
            </div>
        </form>
    </div>

    {{-- DATA UTAMA --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden w-full p-4">
        <h3 class="text-xl font-semibold mb-4 border-b pb-2">Daftar Asset dalam Pencarian & Riwayat Transaksi</h3>

        {{-- Loop pada Finder Data --}}
        @forelse ($assetFinders as $finder)

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 border-b py-4 hover:bg-gray-50">

            {{-- KOLOM KIRI (FINDER DATA) - 2/5 Bagian --}}
            <div class="lg:col-span-2 space-y-2 border-r pr-4">
                <h4 class="font-bold text-lg text-indigo-700">{{ $finder->asset_id }} ({{ $finder->asset->name ?? 'N/A'
                    }})</h4>

                <div class="flex justify-between text-sm">
                    <span class="font-semibold">Status Pencarian:</span>
                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                        @if ($finder->status === 'active')
                        <span aria-hidden="true" class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                        <span class="text-green-900">Active</span>
                        @else
                        <span aria-hidden="true" class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                        <span class="text-red-900">Inactive</span>
                        @endif
                    </span>
                </div>

                <p class="text-sm">
                    <span class="font-semibold">Remarks:</span> {{ $finder->remarks }}
                </p>
                <p class="text-xs text-gray-500">Dibuat oleh: {{ $finder->user->name ?? 'Unknown' }}</p>

                <div class="mt-2 space-x-2">
                    <button wire:click="edit({{ $finder->id }})"
                        class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">Edit Status</button>
                    <button wire:click="delete({{ $finder->id }})"
                        onclick="confirm('Yakin ingin hapus?') || event.stopImmediatePropagation()"
                        class="text-red-600 hover:text-red-900 text-xs font-medium">Hapus</button>
                </div>
            </div>

            {{-- KOLOM KANAN (TRANSACTION DATA) - 3/5 Bagian --}}
            <div class="lg:col-span-3">
                <h5 class="font-semibold text-sm mb-2">Riwayat Transaksi Terakhir (Maks. 3)</h5>

                {{-- PENTING: Panggil relasi latestTransactions --}}
                @if ($finder->latestTransactions->count() > 0)
                <div class="space-y-2">
                    @foreach ($finder->latestTransactions as $trans)
                    <div class="p-2 border rounded-md text-xs bg-gray-100">
                        <div class="flex justify-between font-medium">
                            <span class="text-blue-600">Status: {{ $trans->status }}</span>
                            <span class="text-gray-600">Oleh: {{ $trans->user->name ?? 'N/A' }}</span>
                        </div>
                        <p class="mt-1">
                            <span class="text-green-600">Check-in: {{ $trans->check_in ? $trans->check_in->format('Y-m-d
                                H:i') : 'N/A' }}</span> |
                            <span class="text-red-600">Check-out: {{ $trans->check_out ?
                                $trans->check_out->format('Y-m-d H:i') : 'N/A' }}</span>
                        </p>
                        <p class="text-gray-700 italic truncate">Remarks: {{ $trans->remarks }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500 italic">Tidak ada riwayat transaksi ditemukan untuk aset ini.</p>
                @endif
            </div>
        </div>

        @empty
        <p class="py-4 text-center text-gray-500">Belum ada aset yang ditandai untuk pencarian.</p>
        @endforelse
    </div>
</div>
