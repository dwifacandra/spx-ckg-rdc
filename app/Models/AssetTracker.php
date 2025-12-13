<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTracker extends Model
{
    protected $table = 'assets_tracker';
    protected $fillable = [
        'asset_id',
        'remarks',
        'status',
        'created_by',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'code');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(AssetTransaction::class, 'asset_id', 'asset_id')
            ->latest('id');
    }

    public function latestTransactions()
    {
        return $this->hasMany(AssetTransaction::class, 'asset_id', 'asset_id')
            ->latest('id')
            ->limit(3);
    }
}
