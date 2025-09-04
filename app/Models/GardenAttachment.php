<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GardenAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'garden_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'document_type_id',
        'description',
        'uploaded_by',
        'is_verified',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'file_size' => 'integer'
    ];

    // Relationships
    public function garden()
    {
        return $this->belongsTo(Garden::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function uploadedByUser()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeByDocumentType($query, $typeId)
    {
        return $query->where('document_type_id', $typeId);
    }

    // Accessors
    public function getFileSizeFormattedAttribute()
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < 3; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    public function getDocumentTypeTextAttribute()
    {
        return $this->documentType ? $this->documentType->name : 'Unknown';
    }

    public function getIsImageAttribute()
    {
        return str_starts_with($this->file_type, 'image/');
    }

    public function getIsPdfAttribute()
    {
        return $this->file_type === 'application/pdf';
    }

    public function getDownloadUrlAttribute()
    {
        return route('admin.gardens.attachments.download', [
            'garden' => $this->garden_id,
            'attachment' => $this->id
        ]);
    }

    public function getPreviewUrlAttribute()
    {
        if ($this->is_image || $this->is_pdf) {
            return route('admin.gardens.attachments.preview', [
                'garden' => $this->garden_id,
                'attachment' => $this->id
            ]);
        }
        return null;
    }

    // Methods
    public function verify($userId = null)
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $userId ?? auth()->id()
        ]);
    }

    public function unverify()
    {
        $this->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null
        ]);
    }

    public function getFileContent()
    {
        return Storage::get($this->file_path);
    }

    public function deleteFile()
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    // Static methods
    public static function getAllowedMimeTypes()
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'text/plain'
        ];
    }

    public static function getMaxFileSize()
    {
        return 10 * 1024 * 1024; // 10MB in bytes
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            $attachment->deleteFile();
        });
    }
}