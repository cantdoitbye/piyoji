<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRegister extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sales_entry_id',
        'buyer_id',
        'product_name',
        'tea_grade',
        'quantity_kg',
        'rate_per_kg',
        'total_amount',
        'entry_date',
        'status',
        'remarks',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'approved_by',
        'rejected_by',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'quantity_kg' => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Common tea grades
    const TEA_GRADES = [
        'FTGFOP' => 'Finest Tippy Golden Flowery Orange Pekoe',
        'TGFOP' => 'Tippy Golden Flowery Orange Pekoe',
        'GFOP' => 'Golden Flowery Orange Pekoe',
        'FOP' => 'Flowery Orange Pekoe',
        'OP' => 'Orange Pekoe',
        'PEKOE' => 'Pekoe',
        'BOP' => 'Broken Orange Pekoe',
        'BP' => 'Broken Pekoe',
        'PD' => 'Pekoe Dust',
        'Dust' => 'Dust'
    ];

    /**
     * Boot method to auto-calculate total amount
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($salesEntry) {
            // Auto-calculate total amount
            if ($salesEntry->quantity_kg && $salesEntry->rate_per_kg) {
                $salesEntry->total_amount = $salesEntry->quantity_kg * $salesEntry->rate_per_kg;
            }
        });
    }

    /**
     * Get the buyer that owns the sales entry
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    /**
     * Get the user who created the entry
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the entry
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who approved the entry
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected the entry
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Generate unique sales entry ID
     */
    public static function generateSalesEntryId(): string
    {
        $prefix = 'SLE';
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        // Get the last sales entry ID for current day
        $lastEntry = self::where('sales_entry_id', 'like', $prefix . $year . $month . $day . '%')
            ->orderBy('sales_entry_id', 'desc')
            ->first();
        
        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->sales_entry_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . $day . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Approve the sales entry
     */
    public function approve(int $userId, string $remarks = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'remarks' => $remarks,
            'updated_by' => $userId
        ]);
    }

    /**
     * Reject the sales entry
     */
    public function reject(int $userId, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'updated_by' => $userId
        ]);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Check if entry is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if entry is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if entry is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted rate per kg
     */
    public function getFormattedRatePerKgAttribute(): string
    {
        return '₹' . number_format($this->rate_per_kg, 2);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForBuyer($query, int $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function scopeForDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    public function sample(): BelongsTo
{
    return $this->belongsTo(Sample::class, 'sample_id');
}
}