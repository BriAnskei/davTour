<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourImage;
use App\Models\TourBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TourController extends Controller
{
  

    // ─────────────────────────────────────────
    // List all tours (Admin: paginated table view)
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Tour::with('images')->where('is_archived', false);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tours = $query->latest()->paginate(9);

        return view('admin.tours.index', compact('tours'));
    }

    public function archivedTours(Request $request)
    {
        $query = Tour::with('images')->where('is_archived', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $tours = $query->latest()->paginate(9);

        return view('admin.tours.archive', compact('tours'));
    }

    public function toggleArchive($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $tour = Tour::findOrFail($id);

        // Check if we are trying to archive (not restore)
        if (!$tour->is_archived) {
            // Check for active upcoming bookings
            $hasActiveBookings = $tour->bookings()
                ->whereIn('status', [TourBooking::STATUS_PENDING, TourBooking::STATUS_CONFIRMED, TourBooking::STATUS_AWAITING_VALIDATION])
                ->whereHas('tourSchedule', function($q) {
                    $q->where('date', '>=', today());
                })
                ->exists();

            if ($hasActiveBookings) {
                return back()->with('error', 'Cannot archive this tour because it has active upcoming bookings. Please cancel or complete them first.');
            }
        }

        $tour->update(['is_archived' => !$tour->is_archived]);

        // When a tour is archived, we also archive all its schedules (and their bookings)
        if ($tour->is_archived) {
            $schedules = $tour->schedules;
            foreach ($schedules as $schedule) {
                $schedule->update(['is_archived' => true]);
                $schedule->bookings()->update(['is_archived' => true]);
            }
        }

        $message = $tour->is_archived ? 'Tour and its schedules archived.' : 'Tour restored.';
        return back()->with('success', $message);
    }

    // ─────────────────────────────────────────
    // Show a single tour
    // ─────────────────────────────────────────
    public function show($id)
    {
        $tour = Tour::with(['images', 'schedules'])->findOrFail($id);
        return view('admin.tours.show', compact('tour'));
    }

    // ─────────────────────────────────────────
    // Show create form (Admin only)
    // ─────────────────────────────────────────
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        return view('admin.tours.create');
    }

    // ─────────────────────────────────────────
    // Store new tour (Admin only)
    // ─────────────────────────────────────────
 public function store(Request $request)
{
    if (Auth::user()->role !== 'admin') {
        abort(403, 'Unauthorized');
    }

    $request->validate([
        'name'            => 'required|string|max:255',
        'description'     => 'nullable|string',
        'location'        => 'required|string|max:255',
        'price'           => 'required|numeric|min:0',
        'status'          => 'required|in:active,inactive',
        'images_base64'   => 'required|array|min:1|max:5',
        'images_base64.*' => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        $tour = Tour::create($request->only([
            'name', 'description', 'location', 'price', 'status'
        ]));

        foreach ($request->input('images_base64') as $base64) {
            // Must match: data:image/jpeg;base64,....
            if (!preg_match('/^data:(image\/(jpeg|jpg|png));base64,(.+)$/', $base64, $matches)) {
                continue;
            }

            $mimeType  = $matches[1]; // image/jpeg
            $extension = $matches[2]; // jpeg
            $data      = base64_decode($matches[3]);

            // Guard: max 2MB
            if (strlen($data) > 2 * 1024 * 1024) {
                continue;
            }

            $filename = 'tours/' . uniqid() . '_' . time() . '.' . $extension;
            Storage::disk('public')->put($filename, $data);

            TourImage::create([
                'tour_id' => $tour->id,
                'image'   => $filename,
            ]);
        }

        DB::commit();
        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Tour creation failed: ' . $e->getMessage(), [
            'exception' => $e,
            'request'   => $request->except('images_base64')
        ]);
        return back()->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}

    // ─────────────────────────────────────────
    // Show edit form (Admin only)
    // ─────────────────────────────────────────
    public function edit($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $tour = Tour::with('images')->findOrFail($id);
        return view('admin.tours.edit', compact('tour'));
    }

    // ─────────────────────────────────────────
    // Update tour (Admin only)
    // ─────────────────────────────────────────
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $tour = Tour::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'location'       => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'status'         => 'required|in:active,inactive',
            'remove_images'  => 'nullable|array',
            'remove_images.*' => 'exists:tour_images,id',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $tour->update($request->only([
                'name', 'description', 'location', 'price', 'status'
            ]));

            // 1. Remove marked images
            if ($request->filled('remove_images')) {
                $imagesToRemove = TourImage::whereIn('id', $request->remove_images)
                    ->where('tour_id', $tour->id)
                    ->get();

                foreach ($imagesToRemove as $img) {
                    Storage::disk('public')->delete($img->image);
                    $img->delete();
                }
            }

            // 2. Add new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('tours', 'public');
                    TourImage::create([
                        'tour_id' => $tour->id,
                        'image'   => $path,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tour update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'tour_id'   => $id,
                'request'   => $request->except(['images'])
            ]);
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // Delete tour + images (Admin only)
    // ─────────────────────────────────────────
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $tour = Tour::with('images')->findOrFail($id);

        foreach ($tour->images as $img) {
            Storage::disk('public')->delete($img->image);
        }

        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted successfully.');
    }

    // ─────────────────────────────────────────
    // Toggle active/inactive status (Admin only)
    // ─────────────────────────────────────────
    public function toggle($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $tour = Tour::findOrFail($id);
        $tour->update([
            'status' => $tour->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Tour status updated to ' . $tour->status . '.');
    }
}