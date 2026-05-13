<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\TourBooking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    

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
            'pending'   => TourBooking::where('status', 'pending')->count(),
            'confirmed' => TourBooking::where('status', 'confirmed')->count(),
            'cancelled' => TourBooking::where('status', 'cancelled')->count(),
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
}