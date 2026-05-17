<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\TourBooking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

use App\Models\AuditLog;

class AdminController extends Controller
{
    public function getAuditLogs($type)
    {
        $query = AuditLog::orderBy('created_at', 'desc');

        if ($type !== 'all') {
            $modelType = $type === 'tours' ? Tour::class : TourSchedule::class;
            $query->where('auditable_type', $modelType);
        }

        $logs = $query->take(50)->get();

        return response()->json($logs);
    }

    // ─────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────
    public function dashboard()
    {
        $totalTours     = Tour::count();
        $totalSchedules = TourSchedule::where('is_archived', false)->whereDate('date', '>=', today())->count();
        $totalBookings  = TourBooking::where('is_archived', false)->count();
        $totalRevenue   = Payment::where('payment_status', 'completed')->sum('amount');

        $todayBookings   = TourBooking::whereDate('created_at', today())->count();
        $pendingPayments = Payment::where('payment_status', 'pending')->count();
        $upcomingTours   = TourSchedule::where('is_archived', false)->whereDate('date', '>', today())->count();

        $recentBookings = TourBooking::with(['user', 'tourSchedule.tour'])
            ->where('is_archived', false)
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalTours',
            'totalSchedules',
            'totalBookings',
            'totalRevenue',
            'todayBookings',
            'pendingPayments',
            'upcomingTours',
            'recentBookings'
        ));
    }

    // ─────────────────────────────────────────
    // Bookings
    // ─────────────────────────────────────────
    public function bookings(Request $request)
    {
        $query = TourBooking::with(['user', 'tourSchedule.tour', 'seniorImages'])
            ->where('is_archived', false);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by guest name or tour name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tourSchedule.tour', fn($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        $bookings = $query->latest()->paginate(15);

        // Count per status for the filter tabs
        $counts = [
            'pending'             => TourBooking::where('is_archived', false)->where('status', 'pending')->count(),
            'awaiting_validation' => TourBooking::where('is_archived', false)->where('status', 'awaiting_validation')->count(),
            'confirmed'           => TourBooking::where('is_archived', false)->where('status', 'confirmed')->count(),
            'cancelled'           => TourBooking::where('is_archived', false)->where('status', 'cancelled')->count(),
            'rejected'            => TourBooking::where('is_archived', false)->where('status', 'rejected')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'counts'));
    }

    public function archivedBookings(Request $request)
    {
        $query = TourBooking::with(['user', 'tourSchedule.tour', 'seniorImages'])
            ->where('is_archived', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tourSchedule.tour', fn($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        $bookings = $query->latest()->paginate(15);

        return view('admin.bookings.archive', compact('bookings'));
    }

    public function toggleBookingArchive($id)
    {
        $booking = TourBooking::findOrFail($id);
        $booking->update(['is_archived' => !$booking->is_archived]);

        $message = $booking->is_archived ? 'Booking archived.' : 'Booking restored.';
        return back()->with('success', $message);
    }

    // ─────────────────────────────────────────
    // Update Booking Status (Confirm / Cancel)
    // ─────────────────────────────────────────
    public function updateBookingStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled',
        ]);

        $booking = TourBooking::findOrFail($id);

        if ($request->status === 'confirmed' && $booking->status === TourBooking::STATUS_PENDING) {
            return back()->with('error', 'Cannot manually confirm a booking awaiting payment.');
        }

        $booking->update(['status' => $request->status]);

        if ($request->status === 'confirmed') {
            TourBooking::checkSlotsAndNotify($booking->tour_sched_id);

            // Notify Client
            $booking->user->notify(new \App\Notifications\BookingNotification(
                $booking,
                'booking_confirmed',
                "Your booking for {$booking->tourSchedule->tour->name} has been confirmed!"
            ));
        }

        return back()->with('success', 'Booking status updated to ' . $request->status . '.');
    }

    // ─────────────────────────────────────────
    // Users
    // ─────────────────────────────────────────
    public function users(Request $request)
    {
        $query = User::withCount('bookings');

        // Filter by role
        if ($request->filled('role') && in_array($request->role, ['user', 'admin'])) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users      = $query->latest()->paginate(15);
        $totalUsers = User::count();
        $clientCount = User::where('role', 'user')->count();
        $adminCount  = User::where('role', 'admin')->count();

        return view('admin.users.index', compact('users', 'totalUsers', 'clientCount', 'adminCount'));
    }

    // ─────────────────────────────────────────
    // Notifications
    // ─────────────────────────────────────────
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

    // ─────────────────────────────────────────
    // Senior Validation
    // ─────────────────────────────────────────
    public function showValidationPage($id)
    {
        $booking = TourBooking::with(['user', 'tourSchedule.tour', 'seniorImages'])->findOrFail($id);
        
        if ($booking->senior_count === 0) {
            return redirect()->route('admin.bookings.index')->with('error', 'This booking does not require senior validation.');
        }

        return view('admin.bookings.validate', compact('booking'));
    }

    public function validateSeniorBooking(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string',
        ]);

        $booking = TourBooking::findOrFail($id);

        if ($request->action === 'approve') {
            $booking->status = TourBooking::STATUS_PENDING;
            $booking->rejection_reason = null;
            $booking->save();

            $message = 'Senior ID validated! Booking is now awaiting payment.';

            // Notify Client
            $booking->user->notify(new \App\Notifications\BookingNotification(
                $booking,
                'senior_approved',
                "Your Senior Citizen ID has been approved for {$booking->tourSchedule->tour->name}. You can now proceed to payment."
            ));
        } else {
            $booking->status = TourBooking::STATUS_REJECTED;
            $booking->rejection_reason = $request->reason;
            $booking->save();

            $message = 'Senior ID rejected. Booking status updated to rejected.';

            // Notify Client
            $booking->user->notify(new \App\Notifications\BookingNotification(
                $booking,
                'senior_rejected',
                "Senior ID rejected: {$request->reason}. Click to resubmit."
            ));
        }

        return redirect()->route('admin.bookings.index')->with('success', $message);
    }
}