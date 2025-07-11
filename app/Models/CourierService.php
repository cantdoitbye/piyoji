<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourierService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'service_areas',
        'api_endpoint',
        'api_token',
        'api_username',
        'api_password',
        'webhook_url',
        'tracking_url_template',
        'status',
        'remarks'
    ];

    protected $casts = [
        'service_areas' => 'array',
        'status' => 'boolean'
    ];

    protected $hidden = [
        'api_token',
        'api_username',
        'api_password'
    ];

    // Relationships (commented out until modules are implemented)
    // public function shipments()
    // {
    //     return $this->hasMany(Shipment::class);
    // }

    // public function sampleDispatches()
    // {
    //     return $this->hasMany(SampleDispatch::class);
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

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getServiceAreasTextAttribute()
    {
        return is_array($this->service_areas) ? implode(', ', $this->service_areas) : '';
    }

    public function getMaskedApiTokenAttribute()
    {
        return $this->api_token ? '***' . substr($this->api_token, -4) : '';
    }

    public function getTrackingUrlAttribute()
    {
        return $this->tracking_url_template;
    }

    // Mutators
    public function setServiceAreasAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['service_areas'] = json_encode(array_map('trim', explode(',', $value)));
        } else {
            $this->attributes['service_areas'] = json_encode($value);
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function setApiTokenAttribute($value)
    {
        if ($value) {
            $this->attributes['api_token'] = encrypt($value);
        }
    }

    public function setApiPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['api_password'] = encrypt($value);
        }
    }

    // Custom methods
    public function getDecryptedApiToken()
    {
        return $this->api_token ? decrypt($this->api_token) : null;
    }

    public function getDecryptedApiPassword()
    {
        return $this->api_password ? decrypt($this->api_password) : null;
    }

    public function generateTrackingUrl($trackingNumber)
    {
        return str_replace('{tracking_number}', $trackingNumber, $this->tracking_url_template);
    }

    public function canServiceArea($area)
    {
        if (!is_array($this->service_areas)) {
            return false;
        }
        
        return in_array($area, $this->service_areas);
    }

    // Constants
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const COMMON_SERVICE_AREAS = [
        'North India',
        'South India',
        'East India',
        'West India',
        'Central India',
        'Northeast India',
        'National',
        'International'
    ];
}