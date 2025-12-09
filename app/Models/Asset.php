<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
