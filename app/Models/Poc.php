<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poc extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'poc_name',
        'email',
        'phone',
        'designation',
        'poc_type',
        'address',
        'city',
        'state',
        'pincode',
        'status',
        'remarks'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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
        return $query->where('poc_type', $type);
    }

    public function scopeForSellers($query)
    {
        return $query->whereIn('poc_type', ['seller', 'both']);
    }

    public function scopeForBuyers($query)
    {
        return $query->whereIn('poc_type', ['buyer', 'both']);
    }

    // Accessors
    public function getPocTypeTextAttribute()
    {
        return match($this->poc_type) {
            'seller' => 'Seller',
            'buyer' => 'Buyer',
            'both' => 'Both',
            default => 'Unknown'
        };
    }

    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getFullAddressAttribute()
    {
        $addressParts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode
        ]);
        
        return implode(', ', $addressParts);
    }

    // Static methods
    public static function getPocTypeOptions()
    {
        return [
            'seller' => 'Seller',
            'buyer' => 'Buyer',
            'both' => 'Both'
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