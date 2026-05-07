<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TourController extends Controller
{
  

    // ─────────────────────────────────────────
    // List all tours (Admin: paginated table view)
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Tour::with('images');

        if ($request->filled('search')) {
            $q
            uery->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tours = $query->latest()->paginate(9);

        return view('admin.tours.index', compact('tours'));
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
        return redirect()->route('tours.index')->with('success', 'Tour created successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Something went wrong. Please try again.');
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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'status'      => 'required|in:active,inactive',
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $tour->update($request->only([
                'name', 'description', 'location', 'price', 'status'
            ]));

            // If new images are uploaded, replace existing ones
            if ($request->hasFile('images')) {
                foreach ($tour->images as $img) {
                    Storage::disk('public')->delete($img->image);
                    $img->delete();
                }

                foreach ($request->file('images') as $file) {
                    $path = $file->store('tours', 'public');
                    TourImage::create([
                        'tour_id' => $tour->id,
                        'image'   => $path,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('tours.index')->with('success', 'Tour updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong. Please try again.');
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

        return redirect()->route('tours.index')->with('success', 'Tour deleted successfully.');
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