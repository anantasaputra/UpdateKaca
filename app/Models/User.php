<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // use HasApiTokens, HasFactory, Notifiable;
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_blocked',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    // Relationships
    public function photoStrips()
    {
        return $this->hasMany(PhotoStrip::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function uploadedFrames()
    {
        return $this->hasMany(Frame::class, 'uploaded_by');
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }
}