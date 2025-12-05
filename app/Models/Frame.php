<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Frame extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'filename',
        'category_id',
        'is_active',
        'uploaded_by',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function photoStrips()
    {
        return $this->hasMany(PhotoStrip::class);
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return Storage::url('frames/' . $this->filename);
    }

    public function getImagePathAttribute()
    {
        return storage_path('app/public/frames/' . $this->filename);
    }

    // Methods
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    // Delete with file cleanup
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($frame) {
            if (Storage::disk('public')->exists('frames/' . $frame->filename)) {
                Storage::disk('public')->delete('frames/' . $frame->filename);
            }
        });
    }
}