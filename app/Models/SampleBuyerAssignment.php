<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SampleBuyerAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sample_id',
        'buyer_id',
        'assignment_remarks',
        'dispatch_status',
        'assigned_at',
        'assigned_by',
        'dispatched_at',
        'tracking_id'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'dispatched_at' => 'datetime'
    ];

    // Status Constants
    const STATUS_AWAITING_DISPATCH = 'awaiting_dispatch';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FEEDBACK_RECEIVED = 'feedback_received';

    /**
     * Get the sample that owns the assignment
     */
    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }

    /**
     * Get the buyer that owns the assignment
     */
    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    /**
     * Get the user who assigned the sample
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeAwaitingDispatch($query)
    {
        return $query->where('dispatch_status', self::STATUS_AWAITING_DISPATCH);
    }

    public function scopeDispatched($query)
    {
        return $query->where('dispatch_status', self::STATUS_DISPATCHED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('dispatch_status', self::STATUS_DELIVERED);
    }

    // Accessors
    public function getDispatchStatusTextAttribute()
    {
        return match($this->dispatch_status) {
            self::STATUS_AWAITING_DISPATCH => 'Awaiting Dispatch',
            self::STATUS_DISPATCHED => 'Dispatched',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FEEDBACK_RECEIVED => 'Feedback Received',
            default => 'Unknown'
        };
    }

    public function getDispatchStatusBadgeAttribute()
    {
        return match($this->dispatch_status) {
            self::STATUS_AWAITING_DISPATCH => 'badge bg-warning',
            self::STATUS_DISPATCHED => 'badge bg-info',
            self::STATUS_DELIVERED => 'badge bg-success',
            self::STATUS_FEEDBACK_RECEIVED => 'badge bg-primary',
            default => 'badge bg-secondary'
        };
    }
}