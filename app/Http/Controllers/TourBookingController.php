<?php

namespace App\Http\Controllers;

use App\Models\TourBooking;
use App\Models\TourSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TourBookingController extends Controller
{
    // ─────────────────────────────────────────
    // Client: Show booking form for a schedule
    // ─────────────────────────────────────────
    public function create(Request $request)
    {
        $schedule = TourSchedule::with(['tour.images'])
            ->withCount('bookings')
            ->findOrFail($request->schedule_id);

        $remaining = $schedule->slots - $schedule->bookings_count;

        if ($remaining <= 0) {
            return redirect()->route('client.show', $schedule->tour_id)
                ->with('error', 'This schedule is fully booked.');
        }

        // Conflict check: Has the user already booked another tour on this date?
        $requestedDate = $schedule->date;
        $conflict = TourBooking::where('user_id', Auth::id())
            ->where('status', 'confirmed')
            ->whereHas('tourSchedule', function($query) use ($requestedDate) {
                $query->where('date', $requestedDate);
            })
            ->with('tourSchedule.tour')
            ->first();

        if ($conflict) {
            return redirect()->route('client.show', $schedule->tour_id)
                ->with('conflict_booking', [
                    'date' => \Carbon\Carbon::parse($requestedDate)->format('M d, Y'),
                    'tour_name' => $conflict->tourSchedule->tour->name
                ]);
        }

        return view('client.booking', compact('schedule', 'remaining'));
    }

    // ─────────────────────────────────────────
    // Client: Store a new booking
    // ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'tour_sched_id' => 'required|exists:tour_schedules,id',
            'p_count'       => 'required|integer|min:1',
        ]);

        $schedule  = TourSchedule::withCount('bookings')->findOrFail($request->tour_sched_id);
        $remaining = $schedule->slots - $schedule->bookings_count;

        if ($request->p_count > $remaining) {
            return back()->withErrors([
                'p_count' => "Only {$remaining} slot(s) remaining for this schedule."
            ])->withInput();
        }

        // Prevent duplicate booking
        $alreadyBooked = TourBooking::where('user_id', Auth::id())
            ->where('tour_sched_id', $request->tour_sched_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($alreadyBooked) {
            return back()->with('error', 'You already have a booking for this schedule.');
        }

        TourBooking::create([
            'user_id'       => Auth::id(),
            'tour_sched_id' => $request->tour_sched_id,
            'p_count'       => $request->p_count,
            'status'        => 'pending',
        ]);

        return redirect()->route('client.bookings')
            ->with('success', 'Booking placed successfully! Please wait for confirmation.');
    }

    // ─────────────────────────────────────────
    // Client: View own bookings
    // ─────────────────────────────────────────
    public function myBookings()
    {
        $bookings = TourBooking::with(['tourSchedule.tour.images'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('client.bookings', compact('bookings'));
    }

    // ─────────────────────────────────────────
    // Client: Cancel own booking (pending only)
    // ─────────────────────────────────────────
    public function cancel($id)
    {
        $booking = TourBooking::where('user_id', Auth::id())->findOrFail($id);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully.');
    }
}