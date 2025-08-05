<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'tea_grade',
        'tea_grade_description',
        'price_per_kg',
        'currency',
        'quantity',
        'quality_parameters',
        'special_terms',
        'is_active'
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'quantity' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTeaGrade($query, $teaGrade)
    {
        return $query->where('tea_grade', $teaGrade);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return "₹{$this->price_per_kg}/{$this->currency} per kg";
    }

    public function getQuantityRangeAttribute()
    {
        if ($this->quantity) {
            return "{$this->quantity}  kg";
        }
        
        return 'No limit';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    // Custom methods
    public function isWithinQuantityRange($quantity)
    {
        $withinMin = !$this->quantity || $quantity >= $this->quantity;
        $withinMax = !$this->quantity || $quantity <= $this->quantity;
        
        return $withinMin && $withinMax;
    }
}
