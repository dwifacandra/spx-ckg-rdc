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

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'code');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ops_profile(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'ops_id', 'ops_id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeCurrentlyCheckedOut($query)
    {
        return $query->where('status', 'in use')
            ->whereNull('check_out');
    }

    public function isActive(): bool
    {
        return $this->status === 'in use' && is_null($this->check_in);
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete' && !is_null($this->check_in);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overtime' && is_null($this->check_in);
    }

    public function getDurationInHours(): string
    {
        $startTime = $this->check_out;

        if ($this->check_in) {
            $endTime = $this->check_in;
        } else {
            $endTime = now();
        }

        $totalSeconds = abs($startTime->diffInSeconds($endTime));
        $hours = floor($totalSeconds / 3600);
        $remainingSeconds = $totalSeconds % 3600;
        $minutes = floor($remainingSeconds / 60);

        $seconds = $remainingSeconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
