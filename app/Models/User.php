<?php

namespace App\Models;

// Must extend Authenticatable — NOT base Model — for Auth::user() to work
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'password',
        'role',
        'google_id',
    ];

    // Hide sensitive fields from JSON/array output
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // A user has many bookings
    public function bookings()
    {
        return $this->hasMany(TourBooking::class, 'user_id');
    }
}