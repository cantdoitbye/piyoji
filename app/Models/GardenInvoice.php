<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'garden_id',
        'mark_name',
        'invoice_prefix',
        'invoice_number',
         'category_type',    // New field
        'variables',        // New field
        'grade',   
        'bags_packages',
        'total_invoice_weight',
        'packaging_date',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
                'variables' => 'array',
        'packaging_date' => 'date',
        'total_invoice_weight' => 'decimal:3',
        // 'bags_packages' => 'integer'
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_FINALIZED = 'finalized';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function garden()
    {
        return $this->belongsTo(Garden::class);
    }


      public function getFormattedVariablesAttribute()
    {
        if (!$this->variables || !is_array($this->variables)) {
            return 'No variables selected';
        }
        
        return implode(', ', $this->variables);
    }

        public function getCategoryTypeDisplayAttribute()
    {
        $types = [
            'fannings' => 'Fannings',
            'brokens' => 'Brokens',
            'dust' => 'D (Dust)'
        ];
        
        return $types[$this->category_type] ?? $this->category_type;
    }

      // Keep existing methods and add new ones
    public function generateInvoiceNumber($prefix)
    {
        // Since invoice number is now manual, this method can be simplified or removed
        // Keep for backward compatibility
        $latestInvoice = self::where('invoice_prefix', $prefix)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestInvoice || !$latestInvoice->invoice_number) {
            return $prefix . '001';
        }

        $number = (int) substr($latestInvoice->invoice_number, strlen($prefix));
        return $prefix . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function samples()
    {
        return $this->hasMany(GardenInvoiceSample::class);
    }

    // Accessors
    public function getFullInvoiceNumberAttribute()
    {
        return $this->invoice_prefix . $this->invoice_number;
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_FINALIZED => 'Finalized',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-warning',
            self::STATUS_FINALIZED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getSamplesCountAttribute()
    {
        return $this->samples()->count();
    }

    public function getTotalSetsAttribute()
    {
        return $this->samples()->sum('number_of_sets');
    }

    // Methods
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_FINALIZED => 'Finalized',
            self::STATUS_CANCELLED => 'Cancelled'
        ];
    }

    // public static function generateInvoiceNumber($prefix)
    // {
    //     $lastInvoice = self::where('invoice_prefix', $prefix)
    //         ->orderBy('id', 'desc')
    //         ->first();

    //     if (!$lastInvoice) {
    //         return '0001';
    //     }

    //     $lastNumber = intval($lastInvoice->invoice_number);
    //     return str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    // }

    public function updateTotalWeight()
    {
        $this->total_invoice_weight = $this->samples()->sum('total_sample_weight');
        $this->saveQuietly(); // Save without triggering events
        return $this->total_invoice_weight;
    }

    public function canEdit()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canFinalize()
    {
        return $this->status === self::STATUS_DRAFT && $this->samples()->count() > 0;
    }

    public function canCancel()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_FINALIZED]);
    }

    public function hasSamples()
    {
        return $this->samples()->exists();
    }

    // Add sample to invoice
    public function addSample($sampleData)
    {
        $sample = $this->samples()->create($sampleData);
        $this->updateTotalWeight();
        return $sample;
    }

    // Remove sample from invoice
    public function removeSample($sampleId)
    {
        $sample = $this->samples()->find($sampleId);
        if ($sample) {
            $sample->delete();
            $this->updateTotalWeight();
            return true;
        }
        return false;
    }

    // Get samples summary
    public function getSamplesSummary()
    {
        return [
            'total_samples' => $this->samples()->count(),
            'total_sets' => $this->samples()->sum('number_of_sets'),
            'total_weight' => $this->samples()->sum('total_sample_weight'),
            'average_weight_per_sample' => $this->samples()->avg('sample_weight'),
            'weight_range' => [
                'min' => $this->samples()->min('sample_weight'),
                'max' => $this->samples()->max('sample_weight')
            ]
        ];
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_FINALIZED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeByGarden($query, $gardenId)
    {
        return $query->where('garden_id', $gardenId);
    }

    public function scopeWithSamples($query)
    {
        return $query->with('samples');
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($invoice) {
            // You can add any logic here when invoice is created
        });
    }
}