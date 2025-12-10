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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $this->status === 'in use' && is_null($this->check_in);
    }

    /**
     * Check if transaction is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'complete' && !is_null($this->check_in);
    }

    /**
     * Check if transaction is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overtime' && is_null($this->check_in);
    }

    /**
     * Get the duration in hours, formatted as HH:MM.
     *
     * @return string
     */
    public function getDurationInHours(): string
    {
        // Waktu Mulai (Check Out, menggunakan variabel $this->check_out)
        $startTime = $this->check_out; // <-- Dibalik!

        // Waktu Akhir (Check In, menggunakan variabel $this->check_in atau sekarang)
        if ($this->check_in) {
            $endTime = $this->check_in; // <-- Dibalik!
        } else {
            $endTime = now();
        }

        // 1. Hitung durasi total dalam menit.
        // Menggunakan abs() adalah cara teraman untuk mendapatkan selisih waktu
        // yang selalu positif (absolut) terlepas dari urutan penulisan variabel.
        $totalMinutes = abs($startTime->diffInMinutes($endTime));

        // 2. Konversi ke HH dan MM
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        // 3. Format hasil menjadi HH:MM (dengan leading zero)
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
