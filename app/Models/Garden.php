<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Garden extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'garden_name',
        'address',
        'contact_person_name',
        'mobile_no',
        'email',
        'city',
        'state',
        'pincode',
        'tea_ids',
        'altitude',
        'speciality',
        'status',
        'remarks'
    ];

    protected $casts = [
        'tea_ids' => 'array',
        'altitude' => 'decimal:2',
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

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    // Accessors
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

    public function getAltitudeTextAttribute()
    {
        return $this->altitude ? $this->altitude . ' meters' : 'Not specified';
    }

    // Relationships
    public function teas()
    {
        return $this->belongsToMany(Tea::class, 'garden_tea', 'garden_id', 'tea_id');
    }

    public function selectedTeas()
    {
        if (!$this->tea_ids || !is_array($this->tea_ids)) {
            return collect();
        }
        
        return Tea::whereIn('id', $this->tea_ids)->get();
    }

    // Methods
    public function getTeaNamesAttribute()
    {
        if (!$this->tea_ids || !is_array($this->tea_ids)) {
            return 'No teas selected';
        }
        
        $teas = Tea::whereIn('id', $this->tea_ids)->pluck('sub_title')->toArray();
        return implode(', ', $teas);
    }

    public function hasTeaId($teaId)
    {
        return in_array($teaId, $this->tea_ids ?? []);
    }

    // Static methods
    public static function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }

    public static function getStatesOptions()
    {
        return [
            'Assam' => 'Assam',
            'Darjeeling' => 'Darjeeling',
            'Kerala' => 'Kerala',
            'Tamil Nadu' => 'Tamil Nadu',
            'Karnataka' => 'Karnataka',
            'Himachal Pradesh' => 'Himachal Pradesh',
            'Uttarakhand' => 'Uttarakhand',
            'Arunachal Pradesh' => 'Arunachal Pradesh',
            'Meghalaya' => 'Meghalaya',
            'Manipur' => 'Manipur',
            'Mizoram' => 'Mizoram',
            'Nagaland' => 'Nagaland',
            'Tripura' => 'Tripura',
            'Sikkim' => 'Sikkim'
        ];
    }
}