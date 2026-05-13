<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsAudit;

class TourSchedule extends Model
{
    use LogsAudit;

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