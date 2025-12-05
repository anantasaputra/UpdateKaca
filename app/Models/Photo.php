<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'status',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return Storage::url('photos/' . $this->filename);
    }

    public function getImagePathAttribute()
    {
        return storage_path('app/public/photos/' . $this->filename);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Methods
    public function approve()
    {
        $this->update(['status' => 'approved']);
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }

    // Delete with file cleanup
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            if (Storage::disk('public')->exists('photos/' . $photo->filename)) {
                Storage::disk('public')->delete('photos/' . $photo->filename);
            }
        });
    }
}