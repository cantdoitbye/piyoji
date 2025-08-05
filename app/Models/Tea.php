<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tea_type_id',
        'sub_tea_type_id', 
        'category_id',
        'grade_code',
        'description',
        'characteristics',
        'status',
        'remarks'
    ];

    protected $casts = [
        'characteristics' => 'array',
        'status' => 'boolean',
    ];


    // Static dropdown options as per requirements
    public static function getTeaTypeOptions()
    {
        return [
            'LEAF' => 'LEAF',
            'DUST' => 'DUST'
        ];
    }

    public static function getSubTeaTypeOptions()
    {
        return [
            'Br.Leaf' => 'Br.Leaf',
            'Dust' => 'Dust', 
            'Fn.Leaf' => 'Fn.Leaf'
        ];
    }

    public static function getCategoryOptions()
    {
        return [
            'CTC' => 'CTC',
            'ORTHODOX' => 'ORTHODOX',
            'DARJEELING' => 'DARJEELING'
        ];
    }

    // Helper methods for dependent logic
    public static function getTeaTypesByCategory($category)
    {
        $mapping = [
            'CTC' => ['LEAF', 'DUST'],
            'ORTHODOX' => ['LEAF', 'DUST'], 
            'DARJEELING' => ['LEAF', 'DUST']
        ];
        
        return $mapping[$category] ?? [];
    }

    public static function getGradeCodesByTeaType($teaType)
    {
        // This will fetch from Tea Grade Master in actual implementation
        // For now, return sample data
        $mapping = [
            'LEAF' => ['BP', 'BOP', 'BOPF', 'FOP', 'OP', 'Pekoe'],
            'DUST' => ['PD', 'Dust', 'PF1', 'D1', 'CD']
        ];
        
        return $mapping[$teaType] ?? [];
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

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getFullNameAttribute()
    {
        return "{$this->category} - {$this->tea_type} - {$this->sub_title} ({$this->grade})";
    }

    public function getCharacteristicsTextAttribute()
    {
        if (!$this->characteristics || !is_array($this->characteristics)) {
            return 'Not specified';
        }
        
        return implode(', ', $this->characteristics);
    }

    // Relationships
    public function gardens()
    {
        return $this->belongsToMany(Garden::class, 'garden_tea', 'tea_id', 'garden_id');
    }

  

  

    public static function getGradeOptions()
    {
        return [
            'BP' => 'Broken Pekoe (BP)',
            'BOP' => 'Broken Orange Pekoe (BOP)',
            'BOPF' => 'Broken Orange Pekoe Fannings (BOPF)',
            'PD' => 'Pekoe Dust (PD)',
            'Dust' => 'Dust',
            'FTGFOP' => 'Finest Tippy Golden Flowery Orange Pekoe (FTGFOP)',
            'TGFOP' => 'Tippy Golden Flowery Orange Pekoe (TGFOP)',
            'GFOP' => 'Golden Flowery Orange Pekoe (GFOP)',
            'FOP' => 'Flowery Orange Pekoe (FOP)',
            'OP' => 'Orange Pekoe (OP)',
            'Pekoe' => 'Pekoe',
            'Souchong' => 'Souchong'
        ];
    }

    public static function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }
}