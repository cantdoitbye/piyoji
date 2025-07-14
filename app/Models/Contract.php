<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'contract_number',
        'contract_title',
        'effective_date',
        'expiry_date',
        'status',
        'terms_and_conditions',
        'remarks',
        'uploaded_file_path',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date'
    ];

    // Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function contractItems()
    {
        return $this->hasMany(ContractItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('effective_date', '<=', now())
                     ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now());
    }

    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeByTeaGrade($query, $teaGrade)
    {
        return $query->whereHas('contractItems', function ($q) use ($teaGrade) {
            $q->where('tea_grade', $teaGrade)->where('is_active', true);
        });
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_EXPIRED => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-light'
        };
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return null;
        }
        
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiringAttribute()
    {
        return $this->days_remaining !== null && $this->days_remaining <= 30 && $this->days_remaining >= 0;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date < now();
    }

    public function getValidityPeriodAttribute()
    {
        return $this->effective_date->format('M d, Y') . ' - ' . $this->expiry_date->format('M d, Y');
    }

    public function getTotalItemsAttribute()
    {
        return $this->contractItems()->count();
    }

    public function getActiveItemsAttribute()
    {
        return $this->contractItems()->where('is_active', true)->count();
    }

    // Custom methods
    public function isValid()
    {
        return $this->status === self::STATUS_ACTIVE 
               && $this->effective_date <= now() 
               && $this->expiry_date >= now();
    }

    public function activate()
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function expire()
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    public function cancel()
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function getPriceForTeaGrade($teaGrade)
    {
        $item = $this->contractItems()
                     ->where('tea_grade', $teaGrade)
                     ->where('is_active', true)
                     ->first();
        
        return $item ? $item->price_per_kg : null;
    }

    public function getTeaGradesList()
    {
        return $this->contractItems()
                    ->where('is_active', true)
                    ->pluck('tea_grade')
                    ->toArray();
    }

    public static function generateContractNumber()
    {
        $prefix = 'CON';
        $year = date('Y');
        $month = date('m');
        
        $lastContract = self::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->orderBy('id', 'desc')
                           ->first();
        
        $sequence = $lastContract ? (intval(substr($lastContract->contract_number, -4)) + 1) : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled'
        ];
    }

    // Auto-update status based on dates
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($contract) {
            // Auto-set status based on dates
            if ($contract->effective_date > now()) {
                $contract->status = self::STATUS_DRAFT;
            } elseif ($contract->expiry_date < now()) {
                $contract->status = self::STATUS_EXPIRED;
            } elseif ($contract->status === self::STATUS_DRAFT && $contract->effective_date <= now()) {
                $contract->status = self::STATUS_ACTIVE;
            }
        });
    }
}
