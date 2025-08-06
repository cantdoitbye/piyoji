<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransporterBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'logistic_company_id',
        'branch_name',
        'city',
        'branch_address',
        'branch_contact_person',
        'branch_phone',
        'branch_email',
        'services_offered',
        'operational_hours',
        'handling_capacity_tons_per_day',
        'is_main_branch',
        'status',
        'remarks'
    ];

    protected $casts = [
        'operational_hours' => 'array',
        'handling_capacity_tons_per_day' => 'decimal:2',
        'is_main_branch' => 'boolean',
        'status' => 'boolean'
    ];

    // Constants for cities as per requirement
    const CITIES = [
        'Kolkata' => 'Kolkata',
        'Siliguri' => 'Siliguri',
        'Guwahati' => 'Guwahati'
    ];

    // Relationships
    public function logisticCompany()
    {
        return $this->belongsTo(LogisticCompany::class);
    }

    public function serviceRoutes()
    {
        return $this->hasMany(BranchServiceRoute::class, 'transporter_branch_id');
    }

    public function activeServiceRoutes()
    {
        return $this->hasMany(BranchServiceRoute::class, 'transporter_branch_id');
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

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeMainBranches($query)
    {
        return $query->where('is_main_branch', true);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('logistic_company_id', $companyId);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getFullAddressAttribute()
    {
        return $this->branch_address . ', ' . $this->city;
    }

    public function getFormattedOperationalHoursAttribute()
    {
        if (!$this->operational_hours) {
            return 'Not specified';
        }

        $hours = [];
        foreach ($this->operational_hours as $day => $time) {
            if (isset($time['open']) && isset($time['close'])) {
                $hours[] = ucfirst($day) . ': ' . $time['open'] . ' - ' . $time['close'];
            }
        }

        return implode(', ', $hours);
    }

    public function getHandlingCapacityFormattedAttribute()
    {
        if (!$this->handling_capacity_tons_per_day) {
            return 'Not specified';
        }
        
        return number_format($this->handling_capacity_tons_per_day, 2) . ' tons/day';
    }

    // Methods
    public function getTotalServiceRoutes()
    {
        return $this->serviceRoutes()->count();
    }

    public function getExpressServiceRoutes()
    {
        return $this->serviceRoutes()->where('express_service_available', true)->count();
    }

    public function hasServiceRoute($from, $to)
    {
        return $this->serviceRoutes()
                    ->where('route_from', $from)
                    ->where('route_to', $to)
                    ->exists();
    }

    public function getServiceRoute($from, $to)
    {
        return $this->serviceRoutes()
                    ->where('route_from', $from)
                    ->where('route_to', $to)
                    ->first();
    }

    public function getAverageRatePerKg()
    {
        return $this->serviceRoutes()
                    ->whereNotNull('rate_per_kg')
                    ->avg('rate_per_kg');
    }

    public function canHandleCapacity($requiredTons)
    {
        if (!$this->handling_capacity_tons_per_day) {
            return true; // If capacity not specified, assume it can handle
        }
        
        return $requiredTons <= $this->handling_capacity_tons_per_day;
    }

    // Static methods
    public static function getCityOptions()
    {
        return self::CITIES;
    }

    public static function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }

    public static function getDefaultOperationalHours()
    {
        return [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['open' => '09:00', 'close' => '18:00'],
            'thursday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '18:00'],
            'saturday' => ['open' => '09:00', 'close' => '14:00'],
            'sunday' => ['open' => 'Closed', 'close' => 'Closed']
        ];
    }
}