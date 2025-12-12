<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'ops_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ops_id',
        'staff_name',
        'gender',
        'passport_id',
        'employee_id',
        'blocklist',
        'contract_type',
        'joined_date',
        'last_date',
        'agency',
        'department',
        'station',
        'ops_status',
        'email',
        'soup_role',
    ];

    protected $casts = [
        'blocklist' => 'boolean',
        'joined_date' => 'date',
        'last_date' => 'date',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(AssetTransaction::class, 'ops_id');
    }

    public function accessCardTransactions(): HasMany
    {
        return $this->hasMany(AccessCardTransaction::class, 'ops_id', 'ops_id');
    }
}
