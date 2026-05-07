<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourSchedule extends Model
{
    protected $fillable = [
        'tour_id',
        'date',
        'slots',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    // A schedule has many bookings
    public function bookings()
    {
        return $this->hasMany(TourBooking::class, 'tour_sched_id');
    }
}