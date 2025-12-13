<?php

namespace App\Livewire\Assets;

use Livewire\Component;
use App\Models\AssetTracker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Tracker extends Component
{
    // --- Properti Data Form ---
    public $assetFinders;
    public $asset_id = '';
    public $remarks = '';
    public $status = 'active';

    // --- Properti State ---
    public $is_editing = false;
    public $assetFinderId = null;

    public function mount()
    {
        $this->loadAssetFinders();
    }

    // --- Read Method ---
    public function loadAssetFinders()
    {
        // PENTING: Eager load relasi yang SUDAH dibatasi di model: 'latestTransactions'
        $this->assetFinders = AssetTracker::with([
            'asset',
            'user', // User yang membuat AssetFinder
            'latestTransactions.user', // Memuat relasi user dari transaksi yang terbatas
        ])
            ->latest()
            ->get();
    }

    // --- Edit Method ---
    public function edit($id)
    {
        $this->js('window.scrollTo({top: 0, behavior: "smooth"})');

        $assetFinder = AssetTracker::findOrFail($id);

        $this->assetFinderId = $assetFinder->id;
        $this->asset_id = $assetFinder->asset_id;
        $this->remarks = $assetFinder->remarks;
        $this->status = $assetFinder->status;
        $this->is_editing = true;
    }

    // --- Save (Dispatch Store or Update) ---
    public function save()
    {
        if ($this->is_editing) {
            $this->update();
        } else {
            $this->store();
        }
    }

    // --- Store Method ---
    protected function store()
    {
        $this->validate([
            'asset_id' => [
                'required',
                'string',
                'max:255',
                'exists:assets,code',
                Rule::unique('assets_tracker', 'asset_id'),
            ],
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        AssetTracker::create([
            'asset_id' => $this->asset_id,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'created_by' => Auth::id(),
        ]);

        session()->flash('message', 'Asset Finder berhasil ditambahkan.');
        $this->resetForm();
        $this->loadAssetFinders();
    }

    // --- Update Method ---
    protected function update()
    {
        $this->validate([
            'asset_id' => 'required|string|max:255|exists:assets,code',
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $assetFinder = AssetTracker::findOrFail($this->assetFinderId);
        $assetFinder->update([
            'asset_id' => $this->asset_id,
            'remarks' => $this->remarks,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Asset Finder berhasil diperbarui.');
        $this->resetForm();
        $this->loadAssetFinders();
    }

    // --- Delete Method ---
    public function delete($id)
    {
        AssetTracker::findOrFail($id)->delete();
        session()->flash('message', 'Asset Finder berhasil dihapus.');
        $this->loadAssetFinders();
    }

    // --- Reset Form Method ---
    public function resetForm()
    {
        $this->reset([
            'asset_id',
            'remarks',
            'status',
            'is_editing',
            'assetFinderId',
        ]);
        $this->status = 'active';
    }

    public function render()
    {
        return view('livewire.assets.tracker');
    }
}
