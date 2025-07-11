<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buyer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'buyer_name',
        'buyer_type',
        'contact_person',
        'email',
        'phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_pincode',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_pincode',
        'preferred_tea_grades',
        'status',
        'remarks'
    ];

    protected $casts = [
        'preferred_tea_grades' => 'array',
        'status' => 'boolean'
    ];

    // Relationships (commented out until modules are implemented)
    // public function buyerAssignments()
    // {
    //     return $this->hasMany(BuyerAssignment::class);
    // }

    // public function feedbacks()
    // {
    //     return $this->hasMany(BuyerFeedback::class);
    // }

    // public function purchases()
    // {
    //     return $this->hasMany(Purchase::class);
    // }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeBigBuyers($query)
    {
        return $query->where('buyer_type', self::TYPE_BIG);
    }

    public function scopeSmallBuyers($query)
    {
        return $query->where('buyer_type', self::TYPE_SMALL);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getBuyerTypeTextAttribute()
    {
        return $this->buyer_type === self::TYPE_BIG ? 'Big Buyer' : 'Small Buyer';
    }

    public function getPreferredTeaGradesTextAttribute()
    {
        return is_array($this->preferred_tea_grades) ? implode(', ', $this->preferred_tea_grades) : '';
    }

    public function getFullBillingAddressAttribute()
    {
        return trim("{$this->billing_address}, {$this->billing_city}, {$this->billing_state} - {$this->billing_pincode}");
    }

    public function getFullShippingAddressAttribute()
    {
        return trim("{$this->shipping_address}, {$this->shipping_city}, {$this->shipping_state} - {$this->shipping_pincode}");
    }

    // Mutators
    public function setPreferredTeaGradesAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['preferred_tea_grades'] = json_encode(array_map('trim', explode(',', $value)));
        } else {
            $this->attributes['preferred_tea_grades'] = json_encode($value);
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Constants
    const TYPE_BIG = 'big';
    const TYPE_SMALL = 'small';

    const BUYER_TYPES = [
        self::TYPE_BIG => 'Big Buyer',
        self::TYPE_SMALL => 'Small Buyer'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
}