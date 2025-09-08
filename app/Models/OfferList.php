<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferList extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'awr_no', 
        'date',
        'for',
        'garden_name',
        'garden_id',
        'grade',
        'inv_pretx',
        'inv_no',
        'party_1',
        'party_2', 
        'party_3',
        'party_4',
        'party_5',
        'party_6',
        'party_7',
        'party_8',
        'party_9',
        'party_10',
        'pkgs',
        'net1',
        'ttl_kgs',
        'd_o_packing',
        'type',
        'key',
        'name_of_upload'
    ];

    protected $casts = [
        'date' => 'date',
        'd_o_packing' => 'date',
        'pkgs' => 'decimal:2',
        'net1' => 'decimal:2',
        'ttl_kgs' => 'decimal:3'
    ];

    // Relationships
    public function garden()
    {
        return $this->belongsTo(Garden::class);
    }

    // Scopes
    public function scopeByGarden($query, $gardenId)
    {
        return $query->where('garden_id', $gardenId);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Accessors
    public function getInvPretxTextAttribute()
    {
        return match($this->inv_pretx) {
            'C' => 'Current',
            'EX' => 'Ex',
            'PR' => 'Previous',
            default => 'Unknown'
        };
    }

    public function getForTextAttribute()
    {
        return match($this->for) {
            'GTPP' => 'GTPP',
            'GTFP' => 'GTFP',
            default => 'Unknown'
        };
    }

    // Get all party columns as array
    public function getPartiesAttribute()
    {
        $parties = [];
        for ($i = 1; $i <= 10; $i++) {
            $party = $this->{"party_$i"};
            if (!empty($party)) {
                $parties[] = $party;
            }
        }
        return $parties;
    }

    // Static methods for constants
    public static function getInvPretxOptions()
    {
        return [
            'C' => 'Current',
            'EX' => 'Ex', 
            'PR' => 'Previous'
        ];
    }

    public static function getForOptions()
    {
        return [
            'GTPP' => 'GTPP',
            'GTFP' => 'GTFP'
        ];
    }

    public static function getTypeOptions()
    {
        return [
            'BROKENS' => 'Brokens',
            'FANNINGS' => 'Fannings',
            'D' => 'D'
        ];
    }
}