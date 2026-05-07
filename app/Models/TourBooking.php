<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourBooking extends Model
{
    protected $fillable = [
        'user_id',
        'tour_sched_id',
        'p_count',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Note: foreign key is tour_sched_id (matches your migration)
    public function tourSchedule()
    {
        return $this->belongsTo(TourSchedule::class, 'tour_sched_id');
    }

    // A booking can have payments
    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }
}