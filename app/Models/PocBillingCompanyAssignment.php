<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PocBillingCompanyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'poc_id',
        'billing_company_id',
        'seller_id',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    // Relationships
    public function poc()
    {
        return $this->belongsTo(Poc::class);
    }

    public function billingCompany()
    {
        return $this->belongsTo(BillingCompany::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    // Scopes
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeByPoc($query, $pocId)
    {
        return $query->where('poc_id', $pocId);
    }

    // Static methods
    public static function checkPocAvailability($pocId, $excludeSellerId = null)
    {
        $query = self::where('poc_id', $pocId);
        
        if ($excludeSellerId) {
            $query->where('seller_id', '!=', $excludeSellerId);
        }
        
        return !$query->exists();
    }

    public static function getPocsBySeller($sellerId)
    {
        return self::where('seller_id', $sellerId)
                   ->with('poc')
                   ->get()
                   ->pluck('poc');
    }
}

