<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_status',
        'failure_reason',
        'login_at',
        'logout_at',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
        ];
    }

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get login status display text
     */
    public function getLoginStatusTextAttribute(): string
    {
        return match($this->login_status) {
            'success' => 'Success',
            'failed' => 'Failed',
            default => 'Unknown'
        };
    }

    /**
     * Get session duration
     */
    public function getSessionDurationAttribute(): ?string
    {
        if (!$this->logout_at || !$this->login_at) {
            return null;
        }

        $duration = $this->logout_at->diffInMinutes($this->login_at);
        
        if ($duration < 60) {
            return $duration . ' minutes';
        } else {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    /**
     * Scope for successful logins
     */
    public function scopeSuccessful($query)
    {
        return $query->where('login_status', 'success');
    }

    /**
     * Scope for failed logins
     */
    public function scopeFailed($query)
    {
        return $query->where('login_status', 'failed');
    }

    /**
     * Scope for recent logins
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }
}