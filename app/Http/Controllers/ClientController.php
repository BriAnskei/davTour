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

        // Fetch popular tour images for the header carousel
        // We first try to get tours with at least one booking, ordered by popularity
        $popularTours = Tour::with('images')
            ->where('status', 'active')
            ->whereHas('bookings')
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get();

        // If we don't have enough popular tours (e.g., brand new site), 
        // fill the remaining slots or replace with random tours
        if ($popularTours->count() < 5) {
            $randomTours = Tour::with('images')
                ->where('status', 'active')
                ->whereNotIn('id', $popularTours->pluck('id'))
                ->inRandomOrder()
                ->take(5 - $popularTours->count())
                ->get();
            
            $popularTours = $popularTours->concat($randomTours);
        }

        $carouselImages = $popularTours->map(function($tour) {
            return $tour->images->first() ? asset('storage/' . $tour->images->first()->image) : null;
        })->filter()->values();

        return view('client.index', compact('tours', 'carouselImages'));
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