@extends('layouts.admin')

@section('title', 'Tours')
@section('page-title', 'Tours Management')
@section('page-subtitle', 'Create, edit, and manage all Davao City tours')

@section('content')

{{-- Header Actions --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex gap-2">
        <a href="{{ route('tours.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 hover:shadow-lg"
           style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add New Tour
        </a>
    </div>
    {{-- Search --}}
    <form method="GET" action="{{ route('tours.index') }}" class="flex items-center gap-2">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search tours..."
                   class="pl-9 pr-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white w-64">
        </div>
        <select name="status" class="px-3 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white text-gray-600">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-400 text-white text-sm font-semibold hover:bg-amber-500 transition-colors">Filter</button>
    </form>
</div>

{{-- Tours Grid --}}
@if($tours->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($tours as $tour)
    <div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden group hover:shadow-xl transition-all duration-300">

        {{-- Tour Image --}}
        <div class="relative h-44 bg-jungle-100 overflow-hidden">
            @if($tour->images->isNotEmpty())
                <img src="{{ asset('storage/' . $tour->images->first()->image) }}"
                     alt="{{ $tour->name }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            @else
                <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                    <svg class="w-12 h-12 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
            @endif

            {{-- Status badge --}}
            <div class="absolute top-3 right-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                    {{ $tour->status === 'active' ? 'bg-green-500 text-white' : 'bg-gray-400 text-white' }}">
                    {{ ucfirst($tour->status) }}
                </span>
            </div>

            {{-- Image count --}}
            @if($tour->images->count() > 1)
            <div class="absolute bottom-3 left-3">
                <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-black/40 text-white backdrop-blur-sm">
                    📷 {{ $tour->images->count() }} photos
                </span>
            </div>
            @endif
        </div>

        {{-- Tour Info --}}
        <div class="p-5">
            <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="font-display font-bold text-jungle-700 text-base leading-tight">{{ $tour->name }}</h3>
            </div>

            <div class="flex items-center gap-1.5 text-gray-400 text-xs mb-3">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $tour->location }}
            </div>

            <p class="text-gray-500 text-xs line-clamp-2 mb-4 leading-relaxed">{{ $tour->description ?? 'No description provided.' }}</p>

            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs text-gray-400">Price per person</span>
                    <p class="font-display font-bold text-amber-400 text-lg">₱{{ number_format($tour->price, 2) }}</p>
                </div>
                <div class="flex items-center gap-1.5">
                    {{-- View --}}
                    <a href="{{ route('tours.show', $tour->id) }}"
                       class="w-8 h-8 rounded-lg flex items-center justify-center text-jungle-500 hover:bg-jungle-50 transition-colors" title="View">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    {{-- Schedules --}}
                    <a href="{{ route('tour_schedules.index', ['tour_id' => $tour->id]) }}"
                       class="w-8 h-8 rounded-lg flex items-center justify-center text-amber-400 hover:bg-amber-50 transition-colors" title="Schedules">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </a>
                    {{-- Edit --}}
                    <a href="{{ route('tours.edit', $tour->id) }}"
                       class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-400 hover:bg-blue-50 transition-colors" title="Edit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    {{-- Toggle Status --}}
                    <form method="POST" action="{{ route('tours.toggle', $tour->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors {{ $tour->status === 'active' ? 'text-orange-400 hover:bg-orange-50' : 'text-green-400 hover:bg-green-50' }}"
                                title="{{ $tour->status === 'active' ? 'Deactivate' : 'Activate' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </button>
                    </form>
                    {{-- Delete --}}
                    <form method="POST" action="{{ route('tours.destroy', $tour->id) }}"
                          onsubmit="return confirm('Delete this tour and all its images? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 transition-colors" title="Delete">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
    <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#d6ece0;">
        <svg class="w-8 h-8" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
        </svg>
    </div>
    <h3 class="font-display text-jungle-700 font-bold text-lg mb-2">No tours yet</h3>
    <p class="text-gray-400 text-sm mb-6">Start by adding your first Davao City tour.</p>
    <a href="{{ route('tours.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
       style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add First Tour
    </a>
</div>
@endif
@endsection