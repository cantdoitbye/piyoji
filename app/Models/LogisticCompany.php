<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogisticCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'supported_routes',
        'supported_regions',
        'pricing_structure',
        'pricing_type',
        'base_rate',
        'per_kg_rate',
        'per_km_rate',
        'service_description',
        'gstin',
        'pan',
        'status',
        'remarks'
    ];

    protected $casts = [
        'supported_routes' => 'array',
        'supported_regions' => 'array',
        'status' => 'boolean',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'per_km_rate' => 'decimal:2'
    ];

    // Constants
    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;
    
    const PRICING_TYPE_PER_KG = 'per_kg';
    const PRICING_TYPE_PER_KM = 'per_km';
    const PRICING_TYPE_FLAT_RATE = 'flat_rate';
    const PRICING_TYPE_CUSTOM = 'custom';

    const COMMON_ROUTES = [
        'Delhi-Mumbai',
        'Mumbai-Bangalore',
        'Delhi-Kolkata',
        'Chennai-Hyderabad',
        'Pune-Delhi',
        'Kolkata-Chennai',
        'Bangalore-Delhi',
        'Hyderabad-Mumbai',
        'Ahmedabad-Delhi',
        'Kochi-Mumbai'
    ];

    const COMMON_REGIONS = [
        'North India',
        'South India',
        'East India',
        'West India',
        'Central India',
        'Northeast India',
        'Pan India'
    ];

    // Relationships (commented out until modules are implemented)
    // public function dispatches()
    // {
    //     return $this->hasMany(FullDispatch::class);
    // }

    // public function shipments()
    // {
    //     return $this->hasMany(LogisticShipment::class);
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

    public function scopeByRegion($query, $region)
    {
        return $query->whereJsonContains('supported_regions', $region);
    }

    public function scopeByRoute($query, $route)
    {
        return $query->whereJsonContains('supported_routes', $route);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getSupportedRoutesTextAttribute()
    {
        return is_array($this->supported_routes) ? implode(', ', $this->supported_routes) : '';
    }

    public function getSupportedRegionsTextAttribute()
    {
        return is_array($this->supported_regions) ? implode(', ', $this->supported_regions) : '';
    }

    public function getPricingTypeTextAttribute()
    {
        return match($this->pricing_type) {
            self::PRICING_TYPE_PER_KG => 'Per Kg',
            self::PRICING_TYPE_PER_KM => 'Per Km',
            self::PRICING_TYPE_FLAT_RATE => 'Flat Rate',
            self::PRICING_TYPE_CUSTOM => 'Custom',
            default => 'Unknown'
        };
    }

    public function getFormattedPricingAttribute()
    {
        return match($this->pricing_type) {
            self::PRICING_TYPE_PER_KG => "₹{$this->per_kg_rate}/kg",
            self::PRICING_TYPE_PER_KM => "₹{$this->per_km_rate}/km",
            self::PRICING_TYPE_FLAT_RATE => "₹{$this->base_rate}",
            self::PRICING_TYPE_CUSTOM => $this->pricing_structure,
            default => 'Not Set'
        };
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->state} - {$this->pincode}";
    }

    // Mutators
    public function setSupportedRoutesAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['supported_routes'] = json_encode(array_map('trim', explode(',', $value)));
        } else {
            $this->attributes['supported_routes'] = json_encode($value);
        }
    }

    public function setSupportedRegionsAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['supported_regions'] = json_encode(array_map('trim', explode(',', $value)));
        } else {
            $this->attributes['supported_regions'] = json_encode($value);
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function setGstinAttribute($value)
    {
        $this->attributes['gstin'] = $value ? strtoupper($value) : null;
    }

    public function setPanAttribute($value)
    {
        $this->attributes['pan'] = $value ? strtoupper($value) : null;
    }

    // Custom methods
    public function canServiceRoute($route)
    {
        return is_array($this->supported_routes) && in_array($route, $this->supported_routes);
    }

    public function canServiceRegion($region)
    {
        return is_array($this->supported_regions) && in_array($region, $this->supported_regions);
    }

    public function calculatePricing($weight = null, $distance = null)
    {
        return match($this->pricing_type) {
            self::PRICING_TYPE_PER_KG => $weight ? $this->per_kg_rate * $weight : null,
            self::PRICING_TYPE_PER_KM => $distance ? $this->per_km_rate * $distance : null,
            self::PRICING_TYPE_FLAT_RATE => $this->base_rate,
            default => null
        };
    }

    public static function getPricingTypeOptions()
    {
        return [
            self::PRICING_TYPE_PER_KG => 'Per Kg',
            self::PRICING_TYPE_PER_KM => 'Per Km',
            self::PRICING_TYPE_FLAT_RATE => 'Flat Rate',
            self::PRICING_TYPE_CUSTOM => 'Custom'
        ];
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive'
        ];
    }
}