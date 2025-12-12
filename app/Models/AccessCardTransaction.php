<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessCardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_card_id',
        'ops_id',
        'check_out',
        'check_in',
        'status',
        'location',
        'remarks',
    ];

    protected $casts = [
        'check_out' => 'datetime',
        'check_in' => 'datetime',
    ];

    public function accessCard(): BelongsTo
    {
        return $this->belongsTo(AccessCard::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'ops_id', 'ops_id');
    }
}
