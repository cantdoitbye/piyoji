<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category',
        'tea_type', 
        'grade_code',
        'sub_title',
        'description',
        'characteristics',
        'status',
        'remarks'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    // Tea Categories
    const CATEGORIES = [
        'BLACK' => 'Black Tea',
        'GREEN' => 'Green Tea', 
        'WHITE' => 'White Tea',
        'OOLONG' => 'Oolong Tea',
        'SPECIALTY' => 'Specialty Tea'
    ];

    // Tea Types per Category (for dependent dropdown)
    const TEA_TYPES = [
        'BLACK' => [
            'ORTHODOX' => 'Orthodox',
            'CTC' => 'CTC (Crush, Tear, Curl)',
            'DUST' => 'Dust Grade'
        ],
        'GREEN' => [
            'LEAF' => 'Leaf Grade',
            'POWDER' => 'Powder Grade',
            'SENCHA' => 'Sencha'
        ],
        'WHITE' => [
            'SILVER_TIP' => 'Silver Tip',
            'WHITE_PEONY' => 'White Peony'
        ],
        'OOLONG' => [
            'TRADITIONAL' => 'Traditional',
            'MODERN' => 'Modern Process'
        ],
        'SPECIALTY' => [
            'FLAVORED' => 'Flavored Tea',
            'HERBAL' => 'Herbal Tea',
            'BLENDED' => 'Blended Tea'
        ]
    ];

    // Common Grade Codes per Tea Type (for suggestions/autocomplete)
    const COMMON_GRADE_CODES = [
        'ORTHODOX' => ['FTGFOP1', 'FTGFOP', 'TGFOP1', 'TGFOP', 'GFOP', 'FOP', 'OP', 'OP1', 'PEKOE', 'PS'],
        'CTC' => ['BP', 'BOP', 'BOPF', 'OF', 'F', 'PF'],
        'DUST' => ['PD', 'D', 'D1', 'RD', 'CD', 'SRD'],
        'LEAF' => ['GUNPOWDER', 'YOUNG_HYSON', 'HYSON', 'IMPERIAL', 'TWANKAY'],
        'POWDER' => ['GREEN_POWDER', 'MATCHA', 'FINE_POWDER'],
        'SILVER_TIP' => ['WHITE_SILVER_TIP', 'PREMIUM_SILVER_TIP'],
        'WHITE_PEONY' => ['WHITE_PEONY_GRADE_1', 'WHITE_PEONY_GRADE_2'],
        'TRADITIONAL' => ['TRADITIONAL_OOLONG', 'HIGH_GRADE_OOLONG'],
        'MODERN' => ['MODERN_OOLONG', 'LIGHT_OOLONG'],
        'FLAVORED' => ['EARL_GREY', 'JASMINE', 'BERGAMOT'],
        'HERBAL' => ['CHAMOMILE', 'PEPPERMINT', 'GINGER'],
        'BLENDED' => ['BREAKFAST_BLEND', 'AFTERNOON_BLEND', 'EVENING_BLEND']
    ];

    // Relationships
    public function gardens()
    {
        return $this->belongsToMany(Garden::class, 'garden_tea', 'tea_id', 'garden_id');
    }

    public function samples()
    {
        return $this->hasMany(Sample::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
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

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByTeaType($query, $teaType)
    {
        return $query->where('tea_type', $teaType);
    }

    public function scopeByGradeCode($query, $gradeCode)
    {
        return $query->where('grade_code', $gradeCode);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getCategoryTextAttribute()
    {
        return self::CATEGORIES[$this->category] ?? 'Unknown';
    }

    public function getTeaTypeTextAttribute()
    {
        $categoryTypes = self::TEA_TYPES[$this->category] ?? [];
        return $categoryTypes[$this->tea_type] ?? 'Unknown';
    }

    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->category_text,
            $this->tea_type_text,
            $this->grade_code,
            $this->sub_title
        ]);
        
        return implode(' - ', $parts);
    }

    public function getShortNameAttribute()
    {
        $parts = array_filter([
            $this->category,
            $this->tea_type,
            $this->grade_code
        ]);
        
        return implode('-', $parts);
    }

    // Static Methods
    public static function getCategoryOptions()
    {
        return self::CATEGORIES;
    }

    public static function getTeaTypesByCategory($category)
    {
        return self::TEA_TYPES[$category] ?? [];
    }

    public static function getCommonGradeCodesByTeaType($teaType)
    {
        return self::COMMON_GRADE_CODES[$teaType] ?? [];
    }

    public static function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }

    // Validation Methods
    public static function isValidTeaTypeForCategory($category, $teaType)
    {
        $validTypes = self::TEA_TYPES[$category] ?? [];
        return array_key_exists($teaType, $validTypes);
    }

    // Search Methods for Garden filtering
    public static function getFilteredTeas($categories = [], $teaTypes = [], $gradeCodes = [])
    {
        $query = self::active();
        
        if (!empty($categories)) {
            $query->whereIn('category', $categories);
        }
        
        if (!empty($teaTypes)) {
            $query->whereIn('tea_type', $teaTypes);
        }
        
        if (!empty($gradeCodes)) {
            $query->whereIn('grade_code', $gradeCodes);
        }
        
        return $query->select('id', 'category', 'tea_type', 'grade_code', 'sub_title')
                   ->get()
                   ->map(function($tea) {
                       return [
                           'id' => $tea->id,
                           'full_name' => $tea->full_name,
                           'short_name' => $tea->short_name,
                           'category' => $tea->category,
                           'tea_type' => $tea->tea_type,
                           'grade_code' => $tea->grade_code
                       ];
                   });
    }

    // Get unique grade codes that exist in database for given tea types
    public static function getExistingGradeCodesByTeaTypes($teaTypes = [])
    {
        if (empty($teaTypes)) {
            return [];
        }

        return self::whereIn('tea_type', $teaTypes)
                   ->distinct()
                   ->pluck('grade_code')
                   ->sort()
                   ->values()
                   ->toArray();
    }

    // Export Methods for API responses
    public function toGradingArray()
    {
        return [
            'id' => $this->id,
            'category' => [
                'id' => $this->category,
                'name' => $this->category_text
            ],
            'tea_type' => [
                'id' => $this->tea_type,
                'name' => $this->tea_type_text
            ],
            'grade_code' => $this->grade_code,
            'sub_title' => $this->sub_title,
            'full_name' => $this->full_name,
            'short_name' => $this->short_name,
            'status' => $this->status
        ];
    }
}