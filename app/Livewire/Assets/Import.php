<?php

namespace App\Livewire\Assets;

use Livewire\Component;
use Livewire\WithFileUploads;

class Import extends Component
{
    use WithFileUploads;

    public bool $show = false;
    public $file;

    protected $listeners = ['show' => 'show'];

    public function show(): void
    {
        $this->show = true;
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        // Process the import

        $this->show = false;
        $this->dispatch('refresh');
    }

    public function render()
    {
        return view('livewire.assets.import');
    }
}
