<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenTeaCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'garden_id',
        'category',
        'tea_types',
        'grade_codes'
    ];

    protected $casts = [
        'tea_types' => 'array',
        'grade_codes' => 'array'
    ];

    // Relationships
    public function garden()
    {
        return $this->belongsTo(Garden::class);
    }

    // Accessors
    public function getCategoryTextAttribute()
    {
        return Tea::getCategoryOptions()[$this->category] ?? $this->category;
    }

    public function getTeaTypesTextAttribute()
    {
        if (!$this->tea_types) {
            return [];
        }

        $categoryTeaTypes = Tea::getTeaTypesByCategory($this->category);
        $texts = [];
        
        foreach ($this->tea_types as $teaType) {
            $texts[] = $categoryTeaTypes[$teaType] ?? $teaType;
        }
        
        return $texts;
    }

    public function getTeaTypesCountAttribute()
    {
        return is_array($this->tea_types) ? count($this->tea_types) : 0;
    }

    public function getGradeCodesCountAttribute()
    {
        return is_array($this->grade_codes) ? count($this->grade_codes) : 0;
    }

    public function getHasGradeFilterAttribute()
    {
        return $this->grade_codes_count > 0;
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeWithTeaType($query, $teaType)
    {
        return $query->whereJsonContains('tea_types', $teaType);
    }

    public function scopeWithGradeCode($query, $gradeCode)
    {
        return $query->whereJsonContains('grade_codes', $gradeCode);
    }

    // Methods
    public function hasTeaType($teaType)
    {
        return in_array($teaType, $this->tea_types ?? []);
    }

    public function hasGradeCode($gradeCode)
    {
        return in_array($gradeCode, $this->grade_codes ?? []);
    }

    public function addTeaType($teaType)
    {
        $teaTypes = $this->tea_types ?? [];
        if (!in_array($teaType, $teaTypes)) {
            $teaTypes[] = $teaType;
            $this->tea_types = $teaTypes;
            $this->save();
        }
    }

    public function removeTeaType($teaType)
    {
        $teaTypes = $this->tea_types ?? [];
        $teaTypes = array_values(array_filter($teaTypes, function($type) use ($teaType) {
            return $type !== $teaType;
        }));
        $this->tea_types = $teaTypes;
        $this->save();
    }

    public function addGradeCode($gradeCode)
    {
        $gradeCodes = $this->grade_codes ?? [];
        if (!in_array($gradeCode, $gradeCodes)) {
            $gradeCodes[] = $gradeCode;
            $this->grade_codes = $gradeCodes;
            $this->save();
        }
    }

    public function removeGradeCode($gradeCode)
    {
        $gradeCodes = $this->grade_codes ?? [];
        $gradeCodes = array_values(array_filter($gradeCodes, function($code) use ($gradeCode) {
            return $code !== $gradeCode;
        }));
        $this->grade_codes = $gradeCodes;
        $this->save();
    }

    public function getMatchingTeas()
    {
        return Tea::getFilteredTeas(
            [$this->category], 
            $this->tea_types ?? [], 
            $this->grade_codes ?? []
        );
    }

    // Static methods
    public static function getByGarden($gardenId)
    {
        return self::where('garden_id', $gardenId)->get();
    }

    public static function getByCategory($category)
    {
        return self::where('category', $category)->get();
    }

    public static function getCategorySummaryForGarden($gardenId)
    {
        return self::where('garden_id', $gardenId)
                   ->get()
                   ->map(function($item) {
                       return [
                           'category' => $item->category,
                           'category_text' => $item->category_text,
                           'tea_types_count' => $item->tea_types_count,
                           'grade_codes_count' => $item->grade_codes_count,
                           'has_grade_filter' => $item->has_grade_filter,
                           'tea_types' => $item->tea_types_text
                       ];
                   });
    }
}