<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AssetTransaction extends Model
{
    use HasFactory;

    protected $table = 'assets_transactions';

    protected $fillable = [
        'ops_id',
        'asset_id',
        'check_in',
        'check_out',
        'created_by',
        'status',
        'remarks',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    /**
     * Get the asset that owns the transaction.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Scope to filter transactions by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter transactions by user.
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to get currently checked out assets.
     */
    public function scopeCurrentlyCheckedOut($query)
    {
        return $query->where('status', 'in use')
            ->whereNull('check_out');
    }

    /**
     * Check if transaction is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'in use' && is_null($this->check_out);
    }

    /**
     * Check if transaction is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'complete' && !is_null($this->check_out);
    }

    /**
     * Check if transaction is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overtime' && is_null($this->check_out);
    }

    /**
     * Get the duration in days.
     */
    public function getDurationInDays(): int
    {
        if ($this->check_out) {
            return $this->check_in->diffInDays($this->check_out);
        }

        return $this->check_in->diffInDays(now());
    }
}
