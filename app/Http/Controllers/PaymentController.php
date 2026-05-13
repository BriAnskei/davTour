<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\TourBooking;
use App\Models\TourSchedule;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller
{
    // ─────────────────────────────────────────
    // Admin: View all payments (read-only)
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Payment::with([
            'booking.user',
            'booking.tourSchedule.tour',
        ]);

        if ($request->filled('tour_id')) {
            $query->whereHas('booking.tourSchedule.tour', fn($q) =>
                $q->where('id', $request->tour_id)
            );
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $payments     = $query->latest()->paginate(15);
        $totalRevenue = Payment::where('payment_status', 'completed')->sum('amount');
        $pendingCount = Payment::where('payment_status', 'pending')->count();
        $todayRevenue = Payment::where('payment_status', 'completed')
                            ->whereDate('created_at', today())
                            ->sum('amount');
        $tours = Tour::orderBy('name')->get();

        return view('admin.payments.index', compact(
            'payments', 'totalRevenue', 'pendingCount', 'todayRevenue', 'tours'
        ));
    }

    // ─────────────────────────────────────────
    // Client: Initiate Stripe Checkout
    // Called when client submits the booking form
    // ─────────────────────────────────────────
    public function checkout(Request $request)
    {
        $request->validate([
            'tour_sched_id' => 'required|exists:tour_schedules,id',
            'p_count'       => 'required|integer|min:1',
        ]);

        $schedule  = TourSchedule::with('tour')->withCount('bookings')->findOrFail($request->tour_sched_id);
        $remaining = $schedule->slots - $schedule->bookings_count;

        // Slot check
        if ($request->p_count > $remaining) {
            return back()->withErrors([
                'p_count' => "Only {$remaining} slot(s) remaining."
            ])->withInput();
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
            return back()->with('conflict_booking', [
                'date' => \Carbon\Carbon::parse($requestedDate)->format('M d, Y'),
                'tour_name' => $conflict->tourSchedule->tour->name
            ]);
        }

        // Duplicate booking check
        $alreadyBooked = TourBooking::where('user_id', Auth::id())
            ->where('tour_sched_id', $request->tour_sched_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($alreadyBooked) {
            return back()->with('error', 'You already have a booking for this schedule.');
        }

        // Calculate total
        $totalAmount = $schedule->tour->price * $request->p_count;

        // Create a PENDING booking first
        $booking = TourBooking::create([
            'user_id'       => Auth::id(),
            'tour_sched_id' => $request->tour_sched_id,
            'p_count'       => $request->p_count,
            'status'        => 'pending',
        ]);

        // Create Stripe Checkout Session
        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'php',
                    'unit_amount'  => (int) ($totalAmount * 100), // Stripe uses cents
                    'product_data' => [
                        'name'        => $schedule->tour->name,
                        'description' => 'Tour Date: ' . \Carbon\Carbon::parse($schedule->date)->format('M d, Y')
                                       . ' | ' . $request->p_count . ' person(s)',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('client.payment.success') . '?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
            'cancel_url'  => route('client.booking.create', ['schedule_id' => $request->tour_sched_id]) . '&cancelled=1',
            'metadata'    => [
                'booking_id' => $booking->id,
                'user_id'    => Auth::id(),
            ],
        ]);

        // Redirect to Stripe Checkout
        return redirect($stripeSession->url);
    }

    // ─────────────────────────────────────────
    // Client: Payment Success (Stripe redirects here)
    // ─────────────────────────────────────────
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $bookingId = $request->get('booking_id');

        if (!$sessionId || !$bookingId) {
            return redirect()->route('client.index')->with('error', 'Invalid payment session.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeSession = StripeSession::retrieve($sessionId);

            // Verify payment was successful
            if ($stripeSession->payment_status !== 'paid') {
                return redirect()->route('client.bookings')
                    ->with('error', 'Payment was not completed.');
            }

            $booking = TourBooking::findOrFail($bookingId);

            // Ensure this booking belongs to the logged-in user
            if ($booking->user_id !== Auth::id()) {
                abort(403);
            }

            // Update booking to confirmed
            $booking->update(['status' => 'confirmed']);

            // Create payment record
            Payment::create([
                'booking_id'     => $booking->id,
                'amount'         => $stripeSession->amount_total / 100, // convert back from cents
                'payment_status' => 'completed',
            ]);

        } catch (\Exception $e) {
            return redirect()->route('client.bookings')
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        return redirect()->route('client.bookings')
            ->with('success', 'Payment successful! Your booking is now confirmed. 🎉');
    }

    // ─────────────────────────────────────────
    // Client: Payment Cancelled (Stripe redirects here)
    // ─────────────────────────────────────────
    public function cancel(Request $request)
    {
        // Delete the pending booking that was created before Stripe redirect
        $scheduleId = $request->get('schedule_id');

        if ($scheduleId) {
            // Remove the pending booking for this user + schedule
            TourBooking::where('user_id', Auth::id())
                ->where('tour_sched_id', $scheduleId)
                ->where('status', 'pending')
                ->latest()
                ->first()
                ?->delete();

            return redirect()->route('client.booking.create', ['schedule_id' => $scheduleId])
                ->with('error', 'Payment was cancelled. Please try again.');
        }

        return redirect()->route('client.index')
            ->with('error', 'Payment was cancelled.');
    }

    // ─────────────────────────────────────────
    // Client: View own payments
    // ─────────────────────────────────────────
    public function myPayments()
    {
        $payments = Payment::whereHas('booking', fn($q) => $q->where('user_id', Auth::id()))
            ->with(['booking.tourSchedule.tour'])
            ->latest()
            ->paginate(10);

        return view('client.payments', compact('payments'));
    }
}