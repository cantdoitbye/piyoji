<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_name',
        'tea_estate_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'gstin',
        'pan',
        'tea_grades',
        'status',
        'remarks'
    ];

    protected $casts = [
        'tea_grades' => 'array',
        'status' => 'boolean'
    ];

    // Relationships (commented out until modules are implemented)
    // public function contracts()
    // {
    //     return $this->hasMany(Contract::class);
    // }

    // public function samples()
    // {
    //     return $this->hasMany(Sample::class);
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

    public function getTeaGradesTextAttribute()
    {
        return is_array($this->tea_grades) ? implode(', ', $this->tea_grades) : '';
    }

    // Mutators
    public function setTeaGradesAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['tea_grades'] = json_encode(array_map('trim', explode(',', $value)));
        } else {
            $this->attributes['tea_grades'] = json_encode($value);
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function setGstinAttribute($value)
    {
        $this->attributes['gstin'] = strtoupper($value);
    }

    public function setPanAttribute($value)
    {
        $this->attributes['pan'] = strtoupper($value);
    }

    // Constants
    const TEA_GRADES = [
        'BP' => 'Broken Pekoe',
        'BOP' => 'Broken Orange Pekoe',
        'PD' => 'Pekoe Dust',
        'Dust' => 'Dust',
        'FTGFOP' => 'Finest Tippy Golden Flowery Orange Pekoe',
        'TGFOP' => 'Tippy Golden Flowery Orange Pekoe',
        'GFOP' => 'Golden Flowery Orange Pekoe',
        'FOP' => 'Flowery Orange Pekoe',
        'OP' => 'Orange Pekoe',
        'PEKOE' => 'Pekoe'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
}