<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poc extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'poc_name',
        'email',
        'phone',
        'designation',
        'poc_type',
        'address',
        'city',
        'state',
        'pincode',
        'status',
        'remarks'
    ];

    protected $casts = [
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

    public function scopeByType($query, $type)
    {
        return $query->where('poc_type', $type);
    }

    public function scopeForSellers($query)
    {
        return $query->whereIn('poc_type', ['seller', 'both']);
    }

      public function scopeForTesters($query)
    {
        return $query->where('poc_type', 'tester');
    }

    public function scopeForBuyers($query)
    {
        return $query->whereIn('poc_type', ['buyer', 'both']);
    }

    // Accessors
    public function getPocTypeTextAttribute()
    {
        return match($this->poc_type) {
            'seller' => 'Seller',
            'buyer' => 'Buyer',
            'both' => 'Both',
            'tester' => 'Tester',
            'poc' => 'POC',
            default => 'Unknown'
        };
    }

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

    // Static methods
    public static function getPocTypeOptions()
    {
        return [
            'seller' => 'Seller',
            'buyer' => 'Buyer',
            'tester' => 'Tester',
            'both' => 'Both',
            'poc' => 'POC'
        ];
    }

      /**
     * Check if this POC is a tester
     */
    public function isTester(): bool
    {
        return $this->poc_type === 'tester';
    }

    /**
     * Get tester evaluations for this POC
     */
    public function testerEvaluations()
    {
        return $this->hasMany(BatchTesterEvaluation::class, 'tester_poc_id');
    }

    public static function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }

    /**
     * Get evaluation statistics for this tester
     */
    public function getTesterStatisticsAttribute(): array
    {
        $evaluations = $this->testerEvaluations;
        
        if ($evaluations->isEmpty()) {
            return [
                'total_evaluations' => 0,
                'average_c_score' => 0,
                'average_t_score' => 0,
                'average_s_score' => 0,
                'average_b_score' => 0,
                'average_total_score' => 0,
                'accepted_count' => 0,
                'normal_count' => 0,
                'rejected_count' => 0
            ];
        }

        return [
            'total_evaluations' => $evaluations->count(),
            'average_c_score' => $evaluations->avg('c_score'),
            'average_t_score' => $evaluations->avg('t_score'),
            'average_s_score' => $evaluations->avg('s_score'),
            'average_b_score' => $evaluations->avg('b_score'),
            'average_total_score' => $evaluations->avg(function ($eval) {
                return $eval->total_score;
            }),
            'accepted_count' => $evaluations->filter(function ($eval) {
                return $eval->evaluation_result === 'Accepted';
            })->count(),
            'normal_count' => $evaluations->filter(function ($eval) {
                return $eval->evaluation_result === 'Normal';
            })->count(),
            'rejected_count' => $evaluations->filter(function ($eval) {
                return $eval->evaluation_result === 'Rejected';
            })->count()
        ];
    }
}