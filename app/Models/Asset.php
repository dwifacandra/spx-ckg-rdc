<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

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

    public function transactions(): HasMany
    {
        return $this->hasMany(
            AssetTransaction::class,
            'asset_id',
            'code'
        );
    }

    public function activeTransaction()
    {
        return $this->transactions()
            ->whereIn('status', ['in use', 'overtime'])
            ->whereNull('check_in')
            ->first();
    }

    public function lastTransaction(): HasOne
    {
        return $this->hasOne(AssetTransaction::class, 'asset_id', 'code')
            ->latestOfMany();
    }

    public function isCheckedOut(): bool
    {
        return !is_null($this->activeTransaction());
    }

    public function isAvailable(): bool
    {
        return is_null($this->activeTransaction()) && $this->status === 'complete';
    }
}
