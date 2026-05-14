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
            'tour_sched_id'          => 'required|exists:tour_schedules,id',
            'p_count'                => 'required|integer|min:1',
            'senior_count'           => 'required|integer|min:0|lte:p_count',
            'senior_images_base64'   => 'nullable|array',
            'senior_images_base64.*' => 'nullable|string',
            'existing_senior_images' => 'nullable|array',
            'booking_id'             => 'nullable|exists:tour_bookings,id',
            'tnc'                    => 'required|accepted',
        ]);

        $booking = null;
        if ($request->filled('booking_id')) {
            $booking = TourBooking::with('seniorImages')->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->findOrFail($request->booking_id);
        }

        // Validation for senior IDs
        $newImagesCount = $request->senior_images_base64 ? count($request->senior_images_base64) : 0;
        $existingCount  = $request->existing_senior_images ? count($request->existing_senior_images) : 0;
        $totalImages    = $newImagesCount + $existingCount;

        if ($request->senior_count > 0 && $totalImages === 0) {
            return back()->withErrors(['senior_images_base64' => 'At least one Senior Citizen ID image is required.'])->withInput();
        }

        if ($totalImages > $request->senior_count) {
             return back()->withErrors(['senior_images_base64' => "You can only upload up to {$request->senior_count} ID images."])->withInput();
        }

        $schedule  = TourSchedule::with('tour')->findOrFail($request->tour_sched_id);
        
        // Calculate remaining slots
        $totalPax = TourBooking::where('tour_sched_id', $schedule->id)
            ->whereIn('status', [TourBooking::STATUS_PENDING, TourBooking::STATUS_CONFIRMED, TourBooking::STATUS_AWAITING_VALIDATION])
            ->sum('p_count');

        $othersPax = $totalPax;
        if ($booking && in_array($booking->status, [TourBooking::STATUS_PENDING, TourBooking::STATUS_AWAITING_VALIDATION])) {
            $othersPax -= $booking->p_count;
        }
        $remaining = $schedule->slots - $othersPax;

        // Slot check
        if ($request->p_count > $remaining) {
            return back()->withErrors([
                'p_count' => "Only {$remaining} slot(s) remaining."
            ])->withInput();
        }

        // Conflict check
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

        // Duplicate booking check (only for new bookings)
        if (!$booking) {
            $alreadyBooked = TourBooking::where('user_id', Auth::id())
                ->where('tour_sched_id', $request->tour_sched_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($alreadyBooked) {
                return back()->with('error', 'You already have a booking for this schedule.');
            }
        }

        // Calculate total with 20% discount for seniors
        $price = $schedule->tour->price;
        $regularCount = $request->p_count - $request->senior_count;
        $totalAmount  = ($regularCount * $price) + ($request->senior_count * $price * 0.8);

        // Update or Create Booking
        $prevStatus = $booking ? $booking->status : null;
        $newStatus  = $request->senior_count > 0 ? TourBooking::STATUS_AWAITING_VALIDATION : TourBooking::STATUS_PENDING;
        
        // If it was already approved (pending), keep it pending so they can pay.
        if ($prevStatus === TourBooking::STATUS_PENDING && $request->senior_count > 0) {
            $newStatus = TourBooking::STATUS_PENDING;
        }

        if ($booking) {
            $booking->update([
                'p_count'         => $request->p_count,
                'senior_count'    => $request->senior_count,
                'status'          => $newStatus,
            ]);
        } else {
            $booking = TourBooking::create([
                'user_id'         => Auth::id(),
                'tour_sched_id'   => $request->tour_sched_id,
                'p_count'         => $request->p_count,
                'senior_count'    => $request->senior_count,
                'status'          => $newStatus,
            ]);
        }

        // Load relationships for notification
        $booking->load(['user', 'tourSchedule.tour']);

        // Handle Senior Images (Base64)
        if ($request->senior_images_base64) {
            foreach ($request->senior_images_base64 as $base64) {
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $imageData = substr($base64, strpos($base64, ',') + 1);
                    $imageData = base64_decode($imageData);
                    $extension = strtolower($type[1]); // png, jpg, etc.

                    $filename  = 'senior_ids/' . uniqid() . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $imageData);

                    \App\Models\SeniorCitizenImage::create([
                        'booking_id' => $booking->id,
                        'image_path' => $filename,
                    ]);
                }
            }
        }

        // Cleanup existing images if they are no longer in the selection
        if ($request->filled('existing_senior_images')) {
             $booking->seniorImages()->whereNotIn('id', $request->existing_senior_images)->delete();
        } elseif ($booking && $booking->seniorImages()->exists()) {
             // If senior_count is 0 or no existing_senior_images sent, but images exist, clear them
             if ($request->senior_count == 0) {
                 $booking->seniorImages()->delete();
             }
        }

        // --- NOTIFICATIONS ---
        $admins = \App\Models\User::where('role', 'admin')->get();
        
        // 1. All Bookings Notification
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\BookingNotification(
                $booking, 
                'new_booking', 
                "New booking from {$booking->user->name} for {$booking->tourSchedule->tour->name}"
            ));
        }

        // 2. Senior Validation Notification
        if ($booking->status === TourBooking::STATUS_AWAITING_VALIDATION) {
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\BookingNotification(
                    $booking, 
                    'senior_validation', 
                    "Senior ID validation required for booking #{$booking->id}"
                ));
            }

            return redirect()->route('client.bookings')
                ->with('success', 'Booking submitted! Please wait for admin to validate your Senior Citizen ID before proceeding to payment.');
        }

        // --- SLOT NOTIFICATIONS (Check after adding this booking's p_count) ---
        // Note: For now we only check based on confirmed bookings + current pending
        $totalBooked = TourBooking::where('tour_sched_id', $schedule->id)
            ->whereIn('status', [TourBooking::STATUS_PENDING, TourBooking::STATUS_CONFIRMED, TourBooking::STATUS_AWAITING_VALIDATION])
            ->sum('p_count');
        
        $slotsLeft = $schedule->slots - $totalBooked;
        
        if ($slotsLeft <= 0) {
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\BookingNotification(
                    $booking, 
                    'zero_slots', 
                    "Tour {$booking->tourSchedule->tour->name} on " . \Carbon\Carbon::parse($schedule->date)->format('M d') . " is now FULL."
                ));
            }
        } elseif ($slotsLeft <= 2) {
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\BookingNotification(
                    $booking, 
                    'low_slots', 
                    "Only {$slotsLeft} slots left for {$booking->tourSchedule->tour->name} on " . \Carbon\Carbon::parse($schedule->date)->format('M d') . "."
                ));
            }
        }

        // Create Stripe Checkout Session
        Stripe::setApiKey(config('services.stripe.secret'));

        $description = 'Tour Date: ' . \Carbon\Carbon::parse($schedule->date)->format('M d, Y') . ' | ' . $request->p_count . ' person(s)';
        if ($request->senior_count > 0) {
            $description .= " (Incl. {$request->senior_count} Senior Citizen discount)";
        }

        $stripeSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'php',
                    'unit_amount'  => (int) ($totalAmount * 100),
                    'product_data' => [
                        'name'        => $schedule->tour->name,
                        'description' => $description,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('client.payment.success') . '?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
            'cancel_url'  => route('client.payment.cancel') . '?schedule_id=' . $request->tour_sched_id,
            'metadata'    => [
                'booking_id' => $booking->id,
                'user_id'    => Auth::id(),
            ],
        ]);

        return redirect($stripeSession->url);
    }

    // ─────────────────────────────────────────
    // Client: Resume Payment for a PENDING booking
    // ─────────────────────────────────────────
    public function resume($id)
    {
        $booking = TourBooking::where('user_id', Auth::id())
            ->whereIn('status', [TourBooking::STATUS_PENDING, TourBooking::STATUS_REJECTED])
            ->findOrFail($id);

        return redirect()->route('client.booking.create', [
            'schedule_id' => $booking->tour_sched_id,
            'booking_id'  => $booking->id
        ]);
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
            $booking->update(['status' => TourBooking::STATUS_CONFIRMED]);

            // Check slots and notify admin
            TourBooking::checkSlotsAndNotify($booking->tour_sched_id);

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
        $scheduleId = $request->get('schedule_id');

        if ($scheduleId) {
            return redirect()->route('client.booking.create', ['schedule_id' => $scheduleId])
                ->with('error', 'Payment was cancelled. You can complete it later in your "My Bookings" page.');
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