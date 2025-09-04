<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'boolean'
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status ? 'success' : 'secondary';
    }

    // Static methods
    public static function getActiveOptions()
    {
        return self::active()->ordered()->pluck('name', 'id')->toArray();
    }

    public static function getAllOptions()
    {
        return self::ordered()->pluck('name', 'id')->toArray();
    }

    // Relations
    public function buyerAttachments()
    {
        return $this->hasMany(BuyerAttachment::class, 'document_type_id');
    }

    public function gardenAttachments()
    {
        return $this->hasMany(GardenAttachment::class, 'document_type_id');
    }
}