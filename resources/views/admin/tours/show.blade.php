@extends('layouts.admin')

@section('title', $tour->name)
@section('page-title', $tour->name)
@section('page-subtitle', $tour->location)

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: Images + Info --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Image Gallery --}}
        <div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
            @if($tour->images->isNotEmpty())
                <div class="relative h-72 overflow-hidden">
                    <img id="main-img" src="{{ asset('storage/' . $tour->images->first()->image) }}"
                         class="w-full h-full object-cover transition-opacity duration-300" alt="{{ $tour->name }}">
                </div>
                @if($tour->images->count() > 1)
                <div class="p-4 flex gap-3 overflow-x-auto">
                    @foreach($tour->images as $img)
                    <button onclick="document.getElementById('main-img').src='{{ asset('storage/' . $img->image) }}'"
                            class="shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-transparent hover:border-amber-400 transition-colors">
                        <img src="{{ asset('storage/' . $img->image) }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            @else
                <div class="h-48 flex items-center justify-center" style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                    <p class="text-white/40 text-sm">No images uploaded</p>
                </div>
            @endif
        </div>

        {{-- Description --}}
        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">
            <h2 class="font-display text-jungle-700 font-bold text-base mb-3">About This Tour</h2>
            <p class="text-gray-600 text-sm leading-relaxed">{{ $tour->description ?? 'No description provided.' }}</p>
        </div>

        {{-- Schedules for this tour --}}
        <div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate2 flex items-center justify-between">
                <h2 class="font-display text-jungle-700 font-bold text-base">Tour Schedules</h2>
                <a href="{{ route('tour_schedules.create', ['tour_id' => $tour->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white"
                   style="background:#1a3a2a;">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Schedule
                </a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-cream border-b border-slate2">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Available Slots</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate2">
                    @forelse($tour->schedules ?? [] as $sched)
                    <tr class="hover:bg-cream transition-colors">
                        <td class="px-6 py-3 font-medium text-jungle-700">
                            {{ \Carbon\Carbon::parse($sched->date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $sched->slots > 5 ? 'bg-jungle-100 text-jungle-700' : 'bg-red-100 text-red-500' }}">
                                {{ $sched->slots }} slots
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('tour_schedules.edit', $sched->id) }}"
                                   class="text-xs text-blue-400 hover:text-blue-600 font-semibold">Edit</a>
                                <form method="POST" action="{{ route('tour_schedules.destroy', $sched->id) }}" class="inline"
                                      onsubmit="return confirm('Delete this schedule?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 font-semibold">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400 text-sm">No schedules yet for this tour.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Right: Tour Details Card --}}
    <div class="space-y-5">

        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">
            <div class="flex items-center gap-2 mb-5">
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $tour->status === 'active' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                    {{ ucfirst($tour->status) }}
                </span>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Price per Person</p>
                    <p class="font-display text-2xl font-bold text-amber-400">₱{{ number_format($tour->price, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Location</p>
                    <p class="text-sm text-jungle-700 font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $tour->location }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Total Schedules</p>
                    <p class="text-sm text-jungle-700 font-semibold">{{ $tour->schedules->count() ?? 0 }} scheduled dates</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Images</p>
                    <p class="text-sm text-jungle-700 font-semibold">{{ $tour->images->count() }} photo(s)</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Created</p>
                    <p class="text-sm text-gray-600">{{ $tour->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-slate2 space-y-2">
                <a href="{{ route('tours.edit', $tour->id) }}"
                   class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
                   style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Tour
                </a>
                <form method="POST" action="{{ route('tours.destroy', $tour->id) }}"
                      onsubmit="return confirm('Delete this tour permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-red-500 bg-red-50 hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Tour
                    </button>
                </form>
                <a href="{{ route('tours.index') }}"
                   class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-500 bg-gray-50 hover:bg-gray-100 transition-colors">
                    ← Back to Tours
                </a>
            </div>
        </div>

    </div>
</div>
@endsection