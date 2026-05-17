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
            ->where('is_archived', false)
            ->findOrFail($request->schedule_id);

        $totalPax = TourBooking::where('tour_sched_id', $schedule->id)
            ->whereIn('status', [TourBooking::STATUS_PENDING, TourBooking::STATUS_CONFIRMED, TourBooking::STATUS_AWAITING_VALIDATION])
            ->sum('p_count');

        $remaining = $schedule->slots - $totalPax;

        // If we are resuming a booking, the current booking is already in the count
        $existingBooking = null;
        if ($request->filled('booking_id')) {
            $existingBooking = TourBooking::with('seniorImages')->where('user_id', Auth::id())
                ->whereIn('status', [TourBooking::STATUS_PENDING, TourBooking::STATUS_REJECTED])
                ->findOrFail($request->booking_id);
            
            // If it was already in the active count, add it back to remaining
            if ($existingBooking->status !== TourBooking::STATUS_REJECTED) {
                $remaining += $existingBooking->p_count;
            }
        }

        if ($remaining <= 0 && !$existingBooking) {
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

        return view('client.booking', compact('schedule', 'remaining', 'existingBooking'));
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

        $schedule  = TourSchedule::where('is_archived', false)->withCount('bookings')->findOrFail($request->tour_sched_id);
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
            'status'        => TourBooking::STATUS_PENDING,
        ]);

        // Notify admins (generic)
        $newBooking = TourBooking::with(['user', 'tourSchedule.tour'])->where('user_id', Auth::id())->latest()->first();
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\BookingNotification(
                $newBooking,
                'new_booking',
                "New manual booking from " . Auth::user()->name
            ));
        }

        return redirect()->route('client.bookings')
            ->with('success', 'Booking placed successfully! Please wait for confirmation.');
    }

    // ─────────────────────────────────────────
    // Client: View own bookings
    // ─────────────────────────────────────────
    public function myBookings(Request $request)
    {
        $tab = $request->get('tab', 'upcoming');

        $query = TourBooking::with(['tourSchedule.tour.images'])
            ->where('user_id', Auth::id());

        if ($tab === 'past') {
            $query->whereHas('tourSchedule', function ($q) {
                $q->where('date', '<', today());
            })->latest();
        } else {
            $query->whereHas('tourSchedule', function ($q) {
                $q->where('date', '>=', today());
            })->orderBy(
                TourSchedule::select('date')
                    ->whereColumn('tour_schedules.id', 'tour_bookings.tour_sched_id')
                    ->take(1),
                'asc'
            );
        }

        $bookings = $query->paginate(10);

        return view('client.bookings', compact('bookings', 'tab'));
    }

    // ─────────────────────────────────────────
    // Client: Cancel own booking (pending only)
    // ─────────────────────────────────────────
    public function cancel($id)
    {
        $booking = TourBooking::where('user_id', Auth::id())->findOrFail($id);

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully.');
    }

    public function show($id)
    {
        $booking = TourBooking::with(['tourSchedule.tour.images', 'seniorImages', 'payments'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('client.bookings.show', compact('booking'));
    }

    public function getNotifications()
    {
        $notifications = auth()->user()->unreadNotifications;
        return response()->json($notifications);
    }

    public function markNotificationsAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}