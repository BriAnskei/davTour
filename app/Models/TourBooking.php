<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAudit;

class TourBooking extends Model
{
    use LogsAudit;

    const STATUS_PENDING = 'pending';
    const STATUS_AWAITING_VALIDATION = 'awaiting_validation';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'tour_sched_id',
        'p_count',
        'senior_count',
        'senior_id_image',
        'status',
        'is_archived',
        'rejection_reason',
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

    public function seniorImages()
    {
        return $this->hasMany(SeniorCitizenImage::class, 'booking_id');
    }

    public static function checkSlotsAndNotify($scheduleId)
    {
        $schedule = TourSchedule::with('tour')->findOrFail($scheduleId);
        $totalConfirmed = self::where('tour_sched_id', $scheduleId)
            ->where('status', self::STATUS_CONFIRMED)
            ->sum('p_count');
        
        $slotsLeft = $schedule->slots - $totalConfirmed;
        $admins = User::where('role', 'admin')->get();

        if ($slotsLeft <= 0) {
            $contextBooking = self::with(['user', 'tourSchedule.tour'])->where('tour_sched_id', $scheduleId)->latest()->first();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\BookingNotification(
                    $contextBooking, 
                    'zero_slots', 
                    "Tour {$schedule->tour->name} on " . \Carbon\Carbon::parse($schedule->date)->format('M d') . " is now FULL."
                ));
            }
        } elseif ($slotsLeft <= 2) {
            $contextBooking = self::with(['user', 'tourSchedule.tour'])->where('tour_sched_id', $scheduleId)->latest()->first();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\BookingNotification(
                    $contextBooking,
                    'low_slots', 
                    "Only {$slotsLeft} slots left for {$schedule->tour->name} on " . \Carbon\Carbon::parse($schedule->date)->format('M d') . "."
                ));
            }
        }
    }
}