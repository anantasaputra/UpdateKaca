<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Frame extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'image_path',
        'color_code',
        'photo_count',
        'is_active',
        'is_default',
        'uploaded_by',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'photo_count' => 'integer',
        'usage_count' => 'integer',
    ];

    // ✅ Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function photoStrips()
    {
        return $this->hasMany(PhotoStrip::class, 'frame_id');
    }

    // ✅ FIXED: Image URL with comprehensive fallback handling
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            Log::warning('Frame has no image_path', ['frame_id' => $this->id]);
            return asset('images/placeholder-frame.png');
        }

        $basename = basename($this->image_path);
        
        // Try 1: asset() storage path (via symlink)
        $storagePath = 'storage/frames/' . $basename;
        if (file_exists(public_path($storagePath))) {
            return asset($storagePath);
        }
        
        // Try 2: public/frames direct
        if (file_exists(public_path('frames/' . $basename))) {
            return asset('frames/' . $basename);
        }
        
        // Try 3: Storage disk check
        if (Storage::disk('public')->exists($this->image_path)) {
            return Storage::url($this->image_path);
        }

        Log::warning('Frame image not found in any location', [
            'frame_id' => $this->id,
            'image_path' => $this->image_path,
            'basename' => $basename,
            'checked_paths' => [
                'storage' => public_path($storagePath),
                'public_frames' => public_path('frames/' . $basename),
                'disk' => Storage::disk('public')->exists($this->image_path),
            ]
        ]);
        
        return asset('images/placeholder-frame.png');
    }

    // ✅ NEW: Get full system path
    public function getFullPathAttribute()
    {
        return storage_path('app/public/' . $this->image_path);
    }

    // ✅ FIXED: Comprehensive image existence check
    public function imageExists(): bool
    {
        if (!$this->image_path) {
            return false;
        }

        $basename = basename($this->image_path);

        // Check storage/frames/ (via symlink)
        if (file_exists(public_path('storage/frames/' . $basename))) {
            return true;
        }

        // Check public/frames/ direct
        if (file_exists(public_path('frames/' . $basename))) {
            return true;
        }

        // Check Storage disk
        if (Storage::disk('public')->exists($this->image_path)) {
            return true;
        }

        return false;
    }

    // ✅ Can be deleted?
    public function canBeDeleted(): bool
    {
        if ($this->is_default) {
            return false;
        }
        return $this->photoStrips()->count() === 0;
    }

    // ✅ Can be edited?
    public function canBeEdited(): bool
    {
        return true;
    }

    // ✅ Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_default', false);
    }

    public function scopeForPhotoCount($query, $count)
    {
        return $query->where('photo_count', $count);
    }
}
