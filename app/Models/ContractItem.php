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
        'minimum_quantity',
        'maximum_quantity',
        'quality_parameters',
        'special_terms',
        'is_active'
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'maximum_quantity' => 'decimal:2',
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
        if ($this->minimum_quantity && $this->maximum_quantity) {
            return "{$this->minimum_quantity} - {$this->maximum_quantity} kg";
        } elseif ($this->minimum_quantity) {
            return "Min: {$this->minimum_quantity} kg";
        } elseif ($this->maximum_quantity) {
            return "Max: {$this->maximum_quantity} kg";
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
        $withinMin = !$this->minimum_quantity || $quantity >= $this->minimum_quantity;
        $withinMax = !$this->maximum_quantity || $quantity <= $this->maximum_quantity;
        
        return $withinMin && $withinMax;
    }
}
