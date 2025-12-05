<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PhotoStrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'frame_id',
        'photo_data',
        'final_image_path',
        'photo_count',
        'ip_address',
    ];

    protected $casts = [
        'photo_data' => 'array',
        'photo_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function frame()
    {
        return $this->belongsTo(Frame::class);
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return Storage::url($this->final_image_path);
    }

    public function getImagePathAttribute()
    {
        return storage_path('app/public/' . $this->final_image_path);
    }

    // Delete with file cleanup
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($strip) {
            if (Storage::disk('public')->exists($strip->final_image_path)) {
                Storage::disk('public')->delete($strip->final_image_path);
            }
        });
    }
}