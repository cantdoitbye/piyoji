<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingCompanyDispatchAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_company_id',
        'address_label',
        'dispatch_address',
        'dispatch_city',
        'dispatch_state',
        'dispatch_pincode',
        'contact_person',
        'contact_phone',
        'is_default',
        'status'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean'
    ];

    public function billingCompany(): BelongsTo
    {
        return $this->belongsTo(BillingCompany::class);
    }

    public function getFullAddressAttribute(): string
    {
        return $this->dispatch_address . ', ' . $this->dispatch_city . ', ' . $this->dispatch_state . ' - ' . $this->dispatch_pincode;
    }

    public function getStatusTextAttribute(): string
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}