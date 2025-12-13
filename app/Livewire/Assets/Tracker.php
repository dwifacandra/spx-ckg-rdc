<?php

namespace App\Livewire\Assets;

use Livewire\Component;
use App\Models\Asset;
use App\Models\AssetTracker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Tracker extends Component
{
    public $assetTrackers;
    public $scan_code = '';
    public $currentAsset = null;
    public $currentTracker = null;
    public $currentTransactions = [];
    public $isTrackerActive = false;
    public $asset_id = '';
    public $remarks = '';
    public $status = 'active';
    public $is_editing = false;
    public $assetTrackerId = null;

    public function mount()
    {
        $this->loadAssetTrackers();
    }

    public function processScan()
    {
        $this->validate(['scan_code' => 'required|string|max:255'], [], ['scan_code' => 'Kode Scan']);

        $code = $this->scan_code;

        $this->reset(['currentAsset', 'currentTracker', 'currentTransactions', 'isTrackerActive']);

        $asset = Asset::where('code', $code)
            ->orWhere('serial_number', $code)
            ->orWhere('tag', $code)
            ->first();

        if ($asset) {
            $this->currentAsset = $asset;

            $tracker = AssetTracker::where('asset_id', $asset->code)->first();

            if ($tracker) {
                $this->currentTracker = $tracker;
                $this->isTrackerActive = ($tracker->status === 'active');

                $tracker->load([
                    'latestTransactions' => fn($query) => $query->with('user')
                ]);
                $this->currentTransactions = $tracker->latestTransactions;
            } else {
            }
        } else {
        }

        $this->reset(['scan_code']);
        $this->js('$refs.scanInput.focus()');
    }

    public function endTracking()
    {
        if ($this->currentTracker && $this->currentTracker->status === 'active') {

            $tracker = AssetTracker::find($this->currentTracker->id);

            if ($tracker) {
                $tracker->status = 'inactive';
                $tracker->save();

                session()->flash('end_tracking_message', "Asset **{$tracker->asset_id}** berhasil di-END TRACKING (Status: Inactive).");

                $this->reset(['currentAsset', 'currentTracker', 'currentTransactions', 'isTrackerActive']);

                $this->loadAssetTrackers();
            }
        } else {
            session()->flash('end_tracking_error', "Error: Tracker tidak aktif atau data tidak valid.");
        }
        $this->js('$refs.scanInput.focus()');
    }

    public function loadAssetTrackers()
    {
        $this->assetTrackers = AssetTracker::with(['asset', 'user', 'latestTransactions.user'])
            ->latest()
            ->get();
    }

    public function edit($id)
    {
        $this->js('window.scrollTo({top: 0, behavior: "smooth"})');
        $assetTracker = AssetTracker::findOrFail($id);
        $this->assetTrackerId = $assetTracker->id;
        $this->asset_id = $assetTracker->asset_id;
        $this->remarks = $assetTracker->remarks;
        $this->status = $assetTracker->status;
        $this->is_editing = true;
    }

    public function save()
    {
        if ($this->is_editing) {
            $this->update();
        } else {
            $this->store();
        }
    }

    protected function store()
    {
        $this->validate([
            'asset_id' => ['required', 'string', 'max:255', 'exists:assets,code', Rule::unique('assets_tracker', 'asset_id')],
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        AssetTracker::create([
            'asset_id' => $this->asset_id,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'created_by' => Auth::id(),
        ]);

        session()->flash('message', 'Asset Tracker berhasil ditambahkan.');
        $this->resetForm();
        $this->loadAssetTrackers();
    }

    protected function update()
    {
        $this->validate([
            'asset_id' => 'required|string|max:255|exists:assets,code',
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $assetTracker = AssetTracker::findOrFail($this->assetTrackerId);
        $assetTracker->update([
            'asset_id' => $this->asset_id,
            'remarks' => $this->remarks,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Asset Tracker berhasil diperbarui.');
        $this->resetForm();
        $this->loadAssetTrackers();
    }

    public function delete($id)
    {
        AssetTracker::findOrFail($id)->delete();
        session()->flash('message', 'Asset Tracker berhasil dihapus.');
        $this->loadAssetTrackers();
    }

    public function resetForm()
    {
        $this->reset(['asset_id', 'remarks', 'status', 'is_editing', 'assetTrackerId']);
        $this->status = 'active';
    }

    public function render()
    {
        return view('livewire.assets.tracker');
    }
}
