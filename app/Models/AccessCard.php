<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_number',
        'status',
        'remarks',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(AccessCardTransaction::class);
    }
}
