<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsAudit;

class Tour extends Model
{
    use LogsAudit;

    protected $fillable = [
        'name',
        'description',
        'location',
        'price',
        'status',
        'is_archived'
    ];

    public function images()
{
    return $this->hasMany(TourImage::class);
}

public function schedules()
{
    return $this->hasMany(TourSchedule::class, 'tour_id');
}

public function bookings()
{
    return $this->hasManyThrough(TourBooking::class, TourSchedule::class, 'tour_id', 'tour_sched_id');
}
}