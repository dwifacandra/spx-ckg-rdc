<div>
    <h2 class="text-2xl font-bold mb-6">Asset Tracker & Transaction Tracker</h2>

    @if (session()->has('message'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        {{ session('message') }}
    </div>
    @endif

    @if (session()->has('end_tracking_message'))
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">
        {{ session('end_tracking_message') }}
    </div>
    @endif

    @if (session()->has('end_tracking_error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        {{ session('end_tracking_error') }}
    </div>
    @endif

    <div class="bg-white shadow-lg rounded-lg px-6 py-8 mb-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <div class="space-y-4 border-r pr-4">
                <h3 class="text-xl font-semibold mb-4 border-b pb-2">Scan & Pencarian Detail Asset</h3>

                <form wire:submit.prevent="processScan">

                    <div class="mb-4">
                        <label for="scan_code" class="block text-gray-700 text-sm font-bold mb-2">Scan Code (Asset ID,
                            SN, atau Tag)</label>
                        <input type="text" id="scan_code" wire:model.defer="scan_code" x-ref="scanInput"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-xl font-mono"
                            placeholder="Scan atau Ketik Kode" autofocus>
                        @error('scan_code') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex space-x-4 pt-2">
                        @if ($isTrackerActive)
                        <button type="button" wire:click="endTracking"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex-1 transition duration-150">
                            <i class="fas fa-stop mr-1"></i> END TRACKING
                        </button>

                        @endif
                    </div>
                </form>

                @if ($currentAsset)
                <div @class([ 'mt-6 p-4 rounded-lg shadow-inner border-2' , 'bg-green-100 border-green-500'=>
                    $isTrackerActive,
                    'bg-gray-100 border-gray-300' => !$isTrackerActive
                    ])>
                    <h4 class="font-bold text-lg mb-2">
                        Asset Ditemukan: <span class="text-indigo-700">{{ $currentAsset->code }}</span>
                    </h4>

                    <p class="text-sm">Nama Aset: <span class="font-semibold">{{ $currentAsset->name ?? 'N/A' }}</span>
                    </p>
                    <p class="text-sm mb-3">Lokasi: {{ $currentAsset->location ?? 'N/A' }}</p>

                    <div class="my-2 p-2 border border-dashed rounded bg-white">
                        <p class="text-sm font-semibold">Status Tracker Saat Ini (dari DB):</p>
                        @if ($currentTracker)
                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                            @if ($currentTracker->status === 'active')
                            <span aria-hidden="true"
                                class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                            <span class="text-green-900">ACTIVE</span>
                            @else
                            <span aria-hidden="true" class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                            <span class="text-red-900">INACTIVE</span>
                            @endif
                        </span>
                        <p class="text-xs text-gray-500 mt-1">Tracker dibuat oleh: {{ $currentTracker->user->name ??
                            'N/A' }}</p>
                        @else
                        <p class="text-sm text-gray-500 italic">Data Asset Tracker belum tersedia.</p>
                        @endif
                    </div>

                    <h5 class="font-semibold text-sm mb-2 border-t pt-2">3 Riwayat Transaksi Terakhir</h5>
                    @forelse ($currentTransactions as $trans)
                    <div class="p-2 border rounded-md text-xs bg-white mb-1">
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
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 italic">Tidak ada riwayat transaksi ditemukan untuk aset ini.</p>
                    @endforelse
                </div>
                @endif
            </div>

            <div class="space-y-4">
                <h3 class="text-xl font-semibold mb-4 border-b pb-2">
                    {{ $is_editing ? 'Edit Status Manual' : 'Buat Tracker Manual' }}
                </h3>

                <form wire:submit.prevent="save">

                    @if (!$is_editing)
                    <div class="mb-4">
                        <label for="asset_id_manual" class="block text-gray-700 text-sm font-bold mb-2">Asset Code
                            (Manual)</label>
                        <input type="text" id="asset_id_manual" wire:model.defer="asset_id"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Masukkan Kode Asset">
                        @error('asset_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>
                    @else
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Asset Code (Editing)</label>
                        <input type="text" value="{{ $asset_id }}" disabled
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-400 bg-gray-100 leading-tight">
                        <p class="text-xs text-gray-500 mt-1">Kode Asset tidak bisa diubah.</p>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                        <select id="status" wire:model.defer="status"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="remarks" class="block text-gray-700 text-sm font-bold mb-2">Remarks</label>
                        <textarea id="remarks" wire:model.defer="remarks"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-32"
                            placeholder="Catatan tentang status tracker."></textarea>
                        @error('remarks') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-start pt-4 border-t">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 mr-3">
                            {{ $is_editing ? 'Update Status' : 'Simpan Tracker' }}
                        </button>
                        @if ($is_editing)
                        <button wire:click.prevent="resetForm" type="button"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150">
                            Batal Edit
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden w-full p-4">
        <h3 class="text-xl font-semibold mb-4 border-b pb-2">Daftar Asset yang Di-Track & Riwayat Transaksi</h3>

        @forelse ($assetTrackers as $tracker)

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 border-b py-4 hover:bg-gray-50">

            <div class="lg:col-span-2 space-y-2 border-r pr-4">
                <h4 class="font-bold text-lg text-indigo-700">{{ $tracker->asset_id }} ({{ $tracker->asset->name ??
                    'N/A' }})</h4>

                <div class="flex justify-between text-sm">
                    <span class="font-semibold">Status Tracker:</span>
                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                        @if ($tracker->status === 'active')
                        <span aria-hidden="true" class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                        <span class="text-green-900">Active</span>
                        @else
                        <span aria-hidden="true" class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                        <span class="text-red-900">Inactive</span>
                        @endif
                    </span>
                </div>

                <p class="text-sm">
                    <span class="font-semibold">Remarks:</span> {{ $tracker->remarks }}
                </p>
                <p class="text-xs text-gray-500">Dibuat oleh: {{ $tracker->user->name ?? 'Unknown' }}</p>

                <div class="mt-2 space-x-2">
                    <button wire:click="edit({{ $tracker->id }})"
                        class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">Edit Status</button>
                    <button wire:click="delete({{ $tracker->id }})"
                        onclick="confirm('Yakin ingin hapus?') || event.stopImmediatePropagation()"
                        class="text-red-600 hover:text-red-900 text-xs font-medium">Hapus</button>
                </div>
            </div>

            <div class="lg:col-span-3">
                <h5 class="font-semibold text-sm mb-2">Riwayat Transaksi Terakhir (Maks. 3)</h5>

                @if ($tracker->latestTransactions->count() > 0)
                <div class="space-y-2">
                    @foreach ($tracker->latestTransactions as $trans)
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
        <p class="py-4 text-center text-gray-500">Belum ada aset yang ditandai untuk di-track.</p>
        @endforelse
    </div>
</div>
