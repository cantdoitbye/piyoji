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
        'sub_title',
        'grade',
        'description',
        'characteristics',
        'status',
        'remarks'
    ];

    protected $casts = [
        'characteristics' => 'array',
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

    // Static methods
    public static function getCategoryOptions()
    {
        return [
            'Black Tea' => 'Black Tea',
            'Green Tea' => 'Green Tea',
            'White Tea' => 'White Tea',
            'Oolong Tea' => 'Oolong Tea',
            'Herbal Tea' => 'Herbal Tea',
            'Specialty Tea' => 'Specialty Tea'
        ];
    }

    public static function getTeaTypeOptions()
    {
        return [
            'Orthodox' => 'Orthodox',
            'CTC' => 'CTC',
            'Specialty' => 'Specialty',
            'Organic' => 'Organic'
        ];
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