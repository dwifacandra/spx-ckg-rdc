<?php

namespace App\Livewire\AccessCard;

use App\Models\AccessCard;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $cards = AccessCard::query()
            ->when($this->search, function ($query) {
                $query->where('card_number', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
                    ->orWhere('remarks', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);
        return view('livewire.access-card.index', compact('cards'));
    }
}
