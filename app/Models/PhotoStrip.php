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
        'guest_session_id', // ✅ NEW: Track guest sessions
        'frame_id',
        'photo_data',
        'final_image_path',
        'photo_count',
        'ip_address',
        'is_saved', // ✅ NEW: Track if user saved to profile
    ];

    protected $casts = [
        'photo_data' => 'array',
        'photo_count' => 'integer',
        'is_saved' => 'boolean', // ✅ NEW
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function frame()
    {
        return $this->belongsTo(Frame::class);
    }

    // ===== ACCESSORS =====

    public function getImageUrlAttribute()
    {
        return Storage::url($this->final_image_path);
    }

    public function getImagePathAttribute()
    {
        return storage_path('app/public/' . $this->final_image_path);
    }

    /**
     * ✅ NEW: Check if this strip belongs to a guest
     */
    public function getIsGuestAttribute()
    {
        return is_null($this->user_id) && !is_null($this->guest_session_id);
    }

    /**
     * ✅ NEW: Get user display name (user name or "Guest")
     */
    public function getUserDisplayNameAttribute()
    {
        if ($this->user) {
            return $this->user->name;
        }
        
        return 'Guest (' . substr($this->guest_session_id, 0, 8) . ')';
    }

    // ===== SCOPES =====

    /**
     * ✅ NEW: Scope untuk filter strip yang disimpan
     */
    public function scopeSaved($query)
    {
        return $query->where('is_saved', true);
    }

    /**
     * ✅ NEW: Scope untuk filter strip temporary (belum disimpan)
     */
    public function scopeUnsaved($query)
    {
        return $query->where('is_saved', false);
    }

    /**
     * ✅ NEW: Scope untuk registered users
     */
    public function scopeRegisteredUsers($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * ✅ NEW: Scope untuk guests
     */
    public function scopeGuests($query)
    {
        return $query->whereNull('user_id')
            ->whereNotNull('guest_session_id');
    }

    // ===== MODEL EVENTS =====

    /**
     * ✅ UPDATED: Delete with file cleanup
     */
    public static function boot()
    {
        parent::boot();

        // Auto-delete file saat record dihapus
        static::deleting(function ($strip) {
            if ($strip->final_image_path && Storage::disk('public')->exists($strip->final_image_path)) {
                Storage::disk('public')->delete($strip->final_image_path);
                
                \Log::info('Photo strip file deleted', [
                    'strip_id' => $strip->id,
                    'path' => $strip->final_image_path,
                ]);
            }
        });
    }
}
