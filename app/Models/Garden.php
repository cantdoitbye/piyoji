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
                'garden_type', // New field for garden type
        'contact_person_name',
        'mobile_no',
        'email',
        'city',
        'state',
        'pincode',
        'tea_ids',
        'category_filters',
        'poc_ids',
        'status',
        'remarks',
'acceptable_invoice_types',
        'invoice_type_variables', 



    ];

    protected $casts = [
        'tea_ids' => 'array',
        'category_filters' => 'array',
        'poc_ids' => 'array',
        'status' => 'boolean',
'acceptable_invoice_types' => 'array',
        'invoice_type_variables' => 'array'


    ];


 /**
     * Get garden type options
     */
    public static function getGardenTypeOptions()
    {
        return [
            'garden' => 'Garden',
            'mark' => 'Mark'
        ];
    }

      /**
     * Get text representation of garden type
     */
    public function getGardenTypeTextAttribute()
    {
        $options = self::getGardenTypeOptions();
        return $options[$this->garden_type] ?? 'Not specified';
    }

     /**
     * Get formatted location coordinates
     */
    public function getFormattedLocationAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ', ' . $this->longitude;
        }
        return 'Location not set';
    }

    /**
     * Check if garden has location coordinates
     */
    public function hasLocationAttribute()
    {
        return !empty($this->latitude) && !empty($this->longitude);
    }

    /**
     * Get Google Maps URL for the location
     */
    public function getGoogleMapsUrlAttribute()
    {
        if ($this->has_location) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }


   public static function getInvoiceTypesOptions()
{
    return [
            'fannings' => 'Fannings',
            'brokens' => 'Brokens', 
            'dust' => 'D (Dust)'
        ];
}

  /**
     * Get predefined variables for each invoice type
     */
    public static function getInvoiceTypeVariables()
    {
        return [
            'fannings' => ['UKF', 'CF', 'BJF', 'AGF', 'Other'],
            'brokens' => ['UKB', 'CB', 'BJB', 'AGB', 'Other'],
            'dust' => ['UKD', 'CD', 'BJD', 'AGD', 'PD', 'Other']
        ];
    }

       public static function getAllAvailableVariables()
    {
        $variables = self::getInvoiceTypeVariables();
        $allVariables = [];
        
        foreach ($variables as $type => $vars) {
            foreach ($vars as $var) {
                if (!in_array($var, $allVariables)) {
                    $allVariables[] = $var;
                }
            }
        }
        
        return $allVariables;
    }

// Add this accessor for text display
   /**
     * Get text representation of acceptable invoice types
     */
    public function getAcceptableInvoiceTypesTextAttribute()
    {
        if (!$this->acceptable_invoice_types) {
            return 'Not specified';
        }
        
        $options = self::getInvoiceTypesOptions();
        $selected = array_map(function($type) use ($options) {
            return $options[$type] ?? $type;
        }, $this->acceptable_invoice_types);
        
        return implode(', ', $selected);
    }

     
    public function getFormattedInvoiceTypesWithVariablesAttribute()
    {
        if (!$this->acceptable_invoice_types || !$this->invoice_type_variables) {
            return 'Not specified';
        }

        $options = self::getInvoiceTypesOptions();
        $formatted = [];

        foreach ($this->acceptable_invoice_types as $type) {
            $typeName = $options[$type] ?? $type;
            $variables = $this->invoice_type_variables[$type] ?? [];
            
            if (!empty($variables)) {
                $formatted[] = $typeName . ' (' . implode(', ', $variables) . ')';
            } else {
                $formatted[] = $typeName;
            }
        }

        return implode('; ', $formatted);
    }
 /**
     * Check if garden accepts a specific invoice type
     */
    public function acceptsInvoiceType($type)
    {
        return in_array($type, $this->acceptable_invoice_types ?? []);
    }

    /**
     * Check if garden accepts a specific variable for a type
     */
    public function acceptsVariable($type, $variable)
    {
        $typeVariables = $this->getVariablesForType($type);
        return in_array($variable, $typeVariables);
    }
        /**
     * Get variables for a specific invoice type
     */
    public function getVariablesForType($type)
    {
        return $this->invoice_type_variables[$type] ?? [];
    }


    // Relationships
    public function teas()
    {
        return $this->belongsToMany(Tea::class, 'garden_tea', 'garden_id', 'tea_id');
    }

    public function pocs()
    {
        return $this->belongsToMany(Poc::class, 'garden_poc', 'garden_id', 'poc_id');
    }

    public function sellers()
    {
        return $this->belongsToMany(Seller::class, 'garden_seller', 'garden_id', 'seller_id');
    }

    public function teaCategories()
    {
        return $this->hasMany(GardenTeaCategory::class);
    }

    public function samples()
    {
        return $this->hasMany(Sample::class);
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

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeWithTeas($query, $teaIds = [])
    {
        if (empty($teaIds)) {
            return $query;
        }

        return $query->whereJsonContains('tea_ids', $teaIds);
    }

    public function scopeWithCategories($query, $categories = [])
    {
        if (empty($categories)) {
            return $query;
        }

        return $query->whereHas('teaCategories', function($q) use ($categories) {
            $q->whereIn('category', $categories);
        });
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getFullAddressAttribute()
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode
        ]));
    }

    public function getSelectedTeaVarietiesAttribute()
    {
        if (!$this->tea_ids) {
            return collect();
        }

        return Tea::whereIn('id', $this->tea_ids)->get();
    }

    public function getTeaVarietiesCountAttribute()
    {
        return is_array($this->tea_ids) ? count($this->tea_ids) : 0;
    }

    public function getCategorySummaryAttribute()
    {
        if (!$this->category_filters) {
            return [];
        }

        $summary = [];
        foreach ($this->category_filters as $filter) {
            $categoryName = Tea::getCategoryOptions()[$filter['category']] ?? $filter['category'];
            $teaTypeCount = count($filter['tea_types'] ?? []);
            $gradeCodeCount = count($filter['grade_codes'] ?? []);
            
            $summary[] = [
                'category' => $categoryName,
                'tea_types_count' => $teaTypeCount,
                'grade_codes_count' => $gradeCodeCount,
                'has_grade_filter' => $gradeCodeCount > 0
            ];
        }

        return $summary;
    }

    public function getUniqueCategoriesAttribute()
    {
        if (!$this->category_filters) {
            return [];
        }

        $categories = [];
        foreach ($this->category_filters as $filter) {
            if (isset($filter['category'])) {
                $categories[] = $filter['category'];
            }
        }

        return array_unique($categories);
    }

    public function getAllTeaTypesAttribute()
    {
        if (!$this->category_filters) {
            return [];
        }

        $teaTypes = [];
        foreach ($this->category_filters as $filter) {
            if (isset($filter['tea_types'])) {
                $teaTypes = array_merge($teaTypes, $filter['tea_types']);
            }
        }

        return array_unique($teaTypes);
    }

    public function getAllGradeCodesAttribute()
    {
        if (!$this->category_filters) {
            return [];
        }

        $gradeCodes = [];
        foreach ($this->category_filters as $filter) {
            if (isset($filter['grade_codes'])) {
                $gradeCodes = array_merge($gradeCodes, $filter['grade_codes']);
            }
        }

        return array_unique($gradeCodes);
    }

    // Methods
    public function syncTeaVarieties($teaIds)
    {
        $this->update(['tea_ids' => $teaIds]);
        $this->teas()->sync($teaIds);
    }

    public function syncCategoryFilters($categoryFilters)
    {
        $this->update(['category_filters' => $categoryFilters]);
        
        // Also update the garden_tea_categories table for better querying
        $this->teaCategories()->delete();
        
        foreach ($categoryFilters as $filter) {
            if (isset($filter['category']) && isset($filter['tea_types'])) {
                GardenTeaCategory::create([
                    'garden_id' => $this->id,
                    'category' => $filter['category'],
                    'tea_types' => $filter['tea_types'],
                    'grade_codes' => $filter['grade_codes'] ?? []
                ]);
            }
        }
    }

    public function addCategoryFilter($category, $teaTypes, $gradeCodes = [])
    {
        $filters = $this->category_filters ?? [];
        
        // Check if category already exists, replace if it does
        $existingIndex = null;
        foreach ($filters as $index => $filter) {
            if ($filter['category'] === $category) {
                $existingIndex = $index;
                break;
            }
        }

        $newFilter = [
            'category' => $category,
            'tea_types' => $teaTypes,
            'grade_codes' => $gradeCodes
        ];

        if ($existingIndex !== null) {
            $filters[$existingIndex] = $newFilter;
        } else {
            $filters[] = $newFilter;
        }

        $this->syncCategoryFilters($filters);
    }

    public function removeCategoryFilter($category)
    {
        $filters = $this->category_filters ?? [];
        $filters = array_filter($filters, function($filter) use ($category) {
            return $filter['category'] !== $category;
        });

        $this->syncCategoryFilters(array_values($filters));
    }

    public function hasCategory($category)
    {
        return in_array($category, $this->unique_categories);
    }

    public function hasTeaType($teaType)
    {
        return in_array($teaType, $this->all_tea_types);
    }

    public function hasGradeCode($gradeCode)
    {
        return in_array($gradeCode, $this->all_grade_codes);
    }

    public function getFilteredTeaVarieties()
    {
        if (!$this->category_filters) {
            return collect();
        }

        $allTeas = collect();
        
        foreach ($this->category_filters as $filter) {
            $categories = [$filter['category']];
            $teaTypes = $filter['tea_types'] ?? [];
            $gradeCodes = $filter['grade_codes'] ?? [];
            
            $filteredTeas = Tea::getFilteredTeas($categories, $teaTypes, $gradeCodes);
            $allTeas = $allTeas->merge($filteredTeas);
        }
        
        return $allTeas->unique('id');
    }

    // Static Methods
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
            'West Bengal' => 'West Bengal',
            'Tamil Nadu' => 'Tamil Nadu',
            'Kerala' => 'Kerala',
            'Karnataka' => 'Karnataka',
            'Himachal Pradesh' => 'Himachal Pradesh',
            'Uttarakhand' => 'Uttarakhand',
            'Arunachal Pradesh' => 'Arunachal Pradesh',
            'Manipur' => 'Manipur',
            'Meghalaya' => 'Meghalaya',
            'Mizoram' => 'Mizoram',
            'Nagaland' => 'Nagaland',
            'Sikkim' => 'Sikkim',
            'Tripura' => 'Tripura'
        ];
    }

    // Search and Filter methods
    public static function searchWithFilters($filters = [])
    {
        $query = self::active();

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('garden_name', 'like', "%{$search}%")
                  ->orWhere('contact_person_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        if (isset($filters['state']) && !empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (isset($filters['categories']) && !empty($filters['categories'])) {
            $query->withCategories($filters['categories']);
        }

        if (isset($filters['tea_ids']) && !empty($filters['tea_ids'])) {
            $query->withTeas($filters['tea_ids']);
        }

        return $query;
    }

    // Export methods
    public function toSummaryArray()
    {
        return [
            'id' => $this->id,
            'garden_name' => $this->garden_name,
            'contact_person' => $this->contact_person_name,
            'mobile_no' => $this->mobile_no,
            'email' => $this->email,
            'location' => $this->city . ', ' . $this->state,
            'tea_varieties_count' => $this->tea_varieties_count,
            'categories' => $this->unique_categories,
            'category_summary' => $this->category_summary,
            'status' => $this->status_text,
            'created_at' => $this->created_at
        ];
    }
}