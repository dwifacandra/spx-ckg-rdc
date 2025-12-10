<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'brand',
        'code',
        'type',
        'tag',
        'serial_number',
        'condition',
        'status',
        'remarks',
        'ownership',
    ];

    /**
     * Get all transactions for this asset.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AssetTransaction::class);
    }

    /**
     * Get the currently active transaction for this asset.
     */
    public function activeTransaction()
    {
        return $this->transactions()
            ->where('status', 'in use')
            ->whereNull('check_in')
            ->first();
    }

    /**
     * Check if asset is currently checked out.
     */
    public function isCheckedOut(): bool
    {
        return !is_null($this->activeTransaction());
    }

    /**
     * Check if asset is available.
     */
    public function isAvailable(): bool
    {
        return is_null($this->activeTransaction()) && $this->status === 'complete';
    }
}
