<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenInvoiceSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'garden_invoice_id',
        'sample_code',
        'sample_weight',
        'number_of_sets',
        'total_sample_weight',
        'sample_notes'
    ];

    protected $casts = [
        'sample_weight' => 'decimal:3',
        'number_of_sets' => 'integer',
        'total_sample_weight' => 'decimal:3'
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(GardenInvoice::class, 'garden_invoice_id');
    }

    // Mutators
    public function setTotalSampleWeightAttribute($value)
    {
        // Auto-calculate total sample weight when sample details change
        if ($this->sample_weight && $this->number_of_sets) {
            $this->attributes['total_sample_weight'] = $this->sample_weight * $this->number_of_sets;
        } else {
            $this->attributes['total_sample_weight'] = $value;
        }
    }

    // Methods
    public function calculateTotalWeight()
    {
        $this->total_sample_weight = $this->sample_weight * $this->number_of_sets;
        return $this->total_sample_weight;
    }

    // Accessors
    public function getFormattedWeightAttribute()
    {
        return number_format($this->total_sample_weight, 3) . ' kg';
    }

    public function getSampleIdentifierAttribute()
    {
        return $this->sample_code ?: 'Sample #' . $this->id;
    }

    // Boot method to auto-calculate total weight
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($sample) {
            $sample->total_sample_weight = $sample->sample_weight * $sample->number_of_sets;
        });
        
        static::saved(function ($sample) {
            // Update invoice total weight when sample is saved
            $sample->invoice->updateTotalWeight();
        });
        
        static::deleted(function ($sample) {
            // Update invoice total weight when sample is deleted
            $sample->invoice->updateTotalWeight();
        });
    }
}