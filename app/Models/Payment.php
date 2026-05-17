<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAudit;

class Payment extends Model
{
    use LogsAudit;

    protected $fillable = [
        'booking_id', 
        'amount',
        'payment_status',
        'is_archived',
    ];

    public function booking()
    {
        return $this->belongsTo(TourBooking::class, 'booking_id');
    }
}