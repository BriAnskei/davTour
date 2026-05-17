<?php

namespace App\Http\Controllers;

use App\Models\TourSchedule;   // ← fixed typo (was TourSchdule)
use App\Models\Tour;
use App\Models\TourBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TourScheduleController extends Controller
{
    

    // ─────────────────────────────────────────
    // List all schedules (Admin view)
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = TourSchedule::with(['tour', 'bookings'])
            ->where('is_archived', false);

        // Filter by tour
        if ($request->filled('tour_id')) {
            $query->where('tour_id', $request->tour_id);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->month]);
        }

        $schedules = $query->orderBy('date', 'asc')->paginate(15);
        $tours     = Tour::orderBy('name')->get(); // for filter dropdown

        return view('admin.schedules.index', compact('schedules', 'tours'));
    }

    public function archivedSchedules(Request $request)
    {
        $query = TourSchedule::with(['tour', 'bookings'])
            ->where('is_archived', true);

        // Filter by tour
        if ($request->filled('tour_id')) {
            $query->where('tour_id', $request->tour_id);
        }

        $schedules = $query->orderBy('date', 'desc')->paginate(15);
        $tours     = Tour::orderBy('name')->get();

        return view('admin.schedules.archive', compact('schedules', 'tours'));
    }

    public function toggleArchive($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $schedule = TourSchedule::findOrFail($id);

        // Check if we are trying to archive
        if (!$schedule->is_archived) {
            $hasActiveBookings = $schedule->bookings()
                ->whereIn('status', [TourBooking::STATUS_PENDING, TourBooking::STATUS_CONFIRMED, TourBooking::STATUS_AWAITING_VALIDATION])
                ->exists();

            if ($hasActiveBookings) {
                return back()->with('error', 'Cannot archive this schedule because it has active bookings. Please cancel or complete them first.');
            }
        }

        $schedule->update(['is_archived' => !$schedule->is_archived]);

        // When a schedule is archived, we also archive all its bookings
        if ($schedule->is_archived) {
            $schedule->bookings()->update(['is_archived' => true]);
        }

        $message = $schedule->is_archived ? 'Schedule and its bookings archived.' : 'Schedule restored.';
        return back()->with('success', $message);
    }

    // ─────────────────────────────────────────
    // Show single schedule
    // ─────────────────────────────────────────
    public function show($id)
    {
        $schedule = TourSchedule::with('tour')->findOrFail($id);
        return view('admin.schedules.show', compact('schedule'));
    }

    // ─────────────────────────────────────────
    // Create form (Admin only)
    // ─────────────────────────────────────────
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $tours = Tour::where('status', 'active')->orderBy('name')->get();
        return view('admin.schedules.create', compact('tours'));
    }

    // ─────────────────────────────────────────
    // Store new schedule (Admin only)
    // ─────────────────────────────────────────
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'date'    => 'required|date|after_or_equal:today',
            'slots'   => 'required|integer|min:1',
        ]);

        TourSchedule::create($request->only(['tour_id', 'date', 'slots']));

        return redirect()->route('admin.tour_schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    // ─────────────────────────────────────────
    // Edit form (Admin only)
    // ─────────────────────────────────────────
    public function edit($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $schedule = TourSchedule::with('bookings')->findOrFail($id);
        $tours    = Tour::where('status', 'active')->orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'tours'));
    }

    // ─────────────────────────────────────────
    // Update schedule (Admin only)
    // ─────────────────────────────────────────
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $schedule = TourSchedule::findOrFail($id);

        $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'date'    => 'required|date',
            'slots'   => 'required|integer|min:1',
        ]);

        // Prevent setting slots below the number already booked
        $booked = $schedule->bookings()->count();
        if ($request->slots < $booked) {
            return back()->withErrors([
                'slots' => "Cannot set slots below the {$booked} existing bookings."
            ])->withInput();
        }

        $schedule->update($request->only(['tour_id', 'date', 'slots']));

        return redirect()->route('admin.tour_schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    // ─────────────────────────────────────────
    // Delete schedule (Admin only)
    // ─────────────────────────────────────────
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $schedule = TourSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.tour_schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }
}