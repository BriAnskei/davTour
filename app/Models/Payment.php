<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 
        'amount',
        'payment_status',
    ];

    public function booking()
    {
        return $this->belongsTo(TourBooking::class, 'booking_id');
    }
}