<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::with('images')->where('status', 'active');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $tours = $query->latest()->paginate(9);

        return view('client.index', compact('tours'));
    }

    public function show($id)
    {
        $tour = Tour::with(['images', 'schedules' => function ($q) {
            $q->whereDate('date', '>=', today())
              ->withCount('bookings')
              ->orderBy('date');
        }])->where('status', 'active')->findOrFail($id);

        return view('client.show', compact('tour'));
    }
}