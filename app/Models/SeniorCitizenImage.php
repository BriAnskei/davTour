<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeniorCitizenImage extends Model
{
    protected $fillable = [
        'booking_id',
        'image_path',
    ];

    public function booking()
    {
        return $this->belongsTo(TourBooking::class, 'booking_id');
    }
}