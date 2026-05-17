@extends('layouts.admin')

@section('title', 'Archived Tours')
@section('page-title', 'Tour Archive')
@section('page-subtitle', 'View and restore archived Davao City tours')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <a href="{{ route('admin.tours.index') }}" class="text-xs font-semibold text-gray-400 hover:text-jungle-700 flex items-center gap-1 transition-colors">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Active Tours
    </a>

    <form method="GET" action="{{ route('admin.tours.archive') }}" class="flex items-center gap-2">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search tours..."
                   class="pl-9 pr-4 py-2 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white w-64">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-400 text-white text-sm font-semibold hover:bg-amber-500 transition-colors">Search</button>
    </form>
</div>

{{-- Tours Grid --}}
@if($tours->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($tours as $tour)
    <div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden group grayscale opacity-80">

        {{-- Tour Image --}}
        <div class="relative h-44 bg-jungle-100 overflow-hidden">
            @if($tour->images->isNotEmpty())
                <img src="{{ asset('storage/' . $tour->images->first()->image) }}"
                     alt="{{ $tour->name }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                    <svg class="w-12 h-12 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
            @endif
        </div>

        {{-- Tour Info --}}
        <div class="p-5">
            <h3 class="font-display font-bold text-jungle-700 text-base leading-tight mb-2">{{ $tour->name }}</h3>
            <div class="flex items-center gap-1.5 text-gray-400 text-xs mb-3">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                {{ $tour->location }}
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs text-gray-400 italic">Archived on {{ $tour->updated_at->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <form method="POST" action="{{ route('admin.tours.archive.toggle', $tour->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-jungle-700 text-white hover:opacity-90 transition-colors shadow-sm">
                            Restore Tour
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div class="mt-6">
    {{ $tours->withQueryString()->links() }}
</div>

@else
{{-- Empty State --}}
<div class="bg-white rounded-2xl card-shine border border-slate2 py-20 text-center">
    <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#f3f4f6;">
        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
    </div>
    <p class="text-gray-400 text-sm font-medium">No archived tours found.</p>
</div>
@endif
@endsection
