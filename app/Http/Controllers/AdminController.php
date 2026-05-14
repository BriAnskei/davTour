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
        $totalSchedules = TourSchedule::whereDate('date', '>=', today())->count();
        $totalBookings  = TourBooking::count();
        $totalRevenue   = Payment::where('payment_status', 'completed')->sum('amount');

        $todayBookings   = TourBooking::whereDate('created_at', today())->count();
        $pendingPayments = Payment::where('payment_status', 'pending')->count();
        $upcomingTours   = TourSchedule::whereDate('date', '>', today())->count();

        $recentBookings = TourBooking::with(['user', 'tourSchedule.tour'])
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
        $query = TourBooking::with(['user', 'tourSchedule.tour', 'seniorImages']);

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
            'pending'             => TourBooking::where('status', 'pending')->count(),
            'awaiting_validation' => TourBooking::where('status', 'awaiting_validation')->count(),
            'confirmed'           => TourBooking::where('status', 'confirmed')->count(),
            'cancelled'           => TourBooking::where('status', 'cancelled')->count(),
            'rejected'            => TourBooking::where('status', 'rejected')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'counts'));
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
        $booking->update(['status' => $request->status]);

        if ($request->status === 'confirmed') {
            TourBooking::checkSlotsAndNotify($booking->tour_sched_id);
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
            return redirect()->route('admin.bookings')->with('error', 'This booking does not require senior validation.');
        }

        return view('admin.bookings.validate', compact('booking'));
    }

    public function validateSeniorBooking(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|nullable|string',
        ]);

        $booking = TourBooking::findOrFail($id);

        if ($request->action === 'approve') {
            $booking->update(['status' => TourBooking::STATUS_PENDING]);
            $message = 'Senior ID validated! Booking is now awaiting payment.';
        } else {
            $booking->update(['status' => TourBooking::STATUS_REJECTED]);
            $message = 'Senior ID rejected. Booking status updated to rejected.';
            // Optionally notify user here
        }

        return redirect()->route('admin.bookings')->with('success', $message);
    }
}