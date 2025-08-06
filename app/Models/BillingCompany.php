<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_pincode',
        'gstin',
        'pan',
        'type',
        'status',
        'remarks'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    // Constants
    const TYPE_SELLER = 'seller';
    const TYPE_BUYER = 'buyer';
    const TYPE_BOTH = 'both';

    // Relationships
    public function sellers()
    {
        return $this->belongsToMany(Seller::class, 'seller_billing_companies')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function shippingAddresses()
    {
        return $this->hasMany(BillingCompanyShippingAddress::class);
    }

    public function activeShippingAddresses()
    {
        return $this->hasMany(BillingCompanyShippingAddress::class)->where('status', true);
    }

    public function defaultShippingAddress()
    {
        return $this->hasOne(BillingCompanyShippingAddress::class)->where('is_default', true);
    }

    public function pocAssignments()
    {
        return $this->hasMany(PocBillingCompanyAssignment::class);
    }

    public function pocs()
    {
        return $this->hasManyThrough(Poc::class, PocBillingCompanyAssignment::class, 'billing_company_id', 'id', 'id', 'poc_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSellers($query)
    {
        return $query->whereIn('type', [self::TYPE_SELLER, self::TYPE_BOTH]);
    }

    public function scopeBuyers($query)
    {
        return $query->whereIn('type', [self::TYPE_BUYER, self::TYPE_BOTH]);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getTypeTextAttribute()
    {
        return match($this->type) {
            self::TYPE_SELLER => 'Seller',
            self::TYPE_BUYER => 'Buyer',
            self::TYPE_BOTH => 'Both',
            default => 'Unknown'
        };
    }

    public function getFullAddressAttribute()
    {
        return implode(', ', array_filter([
            $this->billing_address,
            $this->billing_city,
            $this->billing_state,
            $this->billing_pincode
        ]));
    }

    public function getFormattedGstinAttribute()
    {
        if (!$this->gstin) return null;
        return strtoupper($this->gstin);
    }

    public function getFormattedPanAttribute()
    {
        if (!$this->pan) return null;
        return strtoupper($this->pan);
    }

    // Methods
    public function canHaveShippingAddresses()
    {
        return in_array($this->type, [self::TYPE_BUYER, self::TYPE_BOTH]);
    }

    public function getShippingAddressesCount()
    {
        return $this->shippingAddresses()->count();
    }

    public function hasDefaultShippingAddress()
    {
        return $this->defaultShippingAddress()->exists();
    }

    // Static methods
    public static function getTypeOptions()
    {
        return [
            self::TYPE_SELLER => 'Seller',
            self::TYPE_BUYER => 'Buyer',
            self::TYPE_BOTH => 'Both'
        ];
    }

    public static function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }
}