<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCompanyShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_company_id',
        'address_label',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_pincode',
        'contact_person',
        'contact_phone',
        'is_default',
        'status'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean'
    ];

    // Relationships
    public function billingCompany()
    {
        return $this->belongsTo(BillingCompany::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return implode(', ', array_filter([
            $this->shipping_address,
            $this->shipping_city,
            $this->shipping_state,
            $this->shipping_pincode
        ]));
    }

    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }
}