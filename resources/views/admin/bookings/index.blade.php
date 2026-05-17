@extends('layouts.admin')

@section('title', 'Bookings')
@section('page-title', 'All Bookings')
@section('page-subtitle', 'Manage and track all tour reservations')

@section('content')

{{-- Filter Bar --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex gap-2 flex-wrap items-center">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'awaiting_validation' => 'To Validate', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'rejected' => 'Rejected'] as $val => $label)
        <a href="{{ route('admin.bookings.index', ['status' => $val === 'all' ? null : $val]) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors
               {{ (request('status', 'all') === $val || (!request('status') && $val === 'all')) ? 'text-white' : 'bg-white border border-slate2 text-gray-500 hover:bg-gray-50' }}"
           style="{{ (request('status', 'all') === $val || (!request('status') && $val === 'all')) ? 'background:#1a3a2a;' : '' }}">
            {{ $label }}
            @if(isset($counts[$val]) && $val !== 'all')
                <span class="ml-1 opacity-70">({{ $counts[$val] }})</span>
            @endif
        </a>
        @endforeach

        <div class="w-px h-6 bg-slate2 mx-2"></div>

        <a href="{{ route('admin.bookings.archive') }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate2 text-amber-500 hover:bg-amber-50 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            Archived Bookings
        </a>
    </div>

    <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex gap-2">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search guest or tour..."
                   class="pl-9 pr-4 py-2 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white w-56">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-400 text-white text-sm font-semibold hover:bg-amber-500 transition-colors">Search</button>
    </form>
</div>

{{-- Bookings Table --}}
<div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f5f1eb;" class="border-b border-slate2">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Guest</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tour</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Schedule Date</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pax</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Booked On</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate2">
                @forelse($bookings as $booking)
                <tr class="hover:bg-cream transition-colors">
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $booking->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                 style="background:#2d6a4f;">
                                {{ strtoupper(substr($booking->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-jungle-700 text-xs">{{ $booking->user->name ?? '—' }}</p>
                                <p class="text-gray-400 text-xs">{{ $booking->user->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-700 text-xs">{{ $booking->tourSchedule->tour->name ?? '—' }}</p>
                        <p class="text-gray-400 text-xs">{{ $booking->tourSchedule->tour->location ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-xs font-medium">
                        {{ isset($booking->tourSchedule->date) ? \Carbon\Carbon::parse($booking->tourSchedule->date)->format('M d, Y') : '—' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-jungle-50 text-jungle-700">
                            {{ $booking->p_count }} pax
                        </span>
                        @if($booking->senior_count > 0)
                            <div class="mt-1 flex flex-col gap-1">
                                <span class="text-[10px] text-amber-500 font-semibold uppercase tracking-tighter">
                                    Incl. {{ $booking->senior_count }} Senior(s)
                                </span>
                                @if($booking->seniorImages->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($booking->seniorImages as $img)
                                            <a href="{{ asset('storage/' . $img->image_path) }}" target="_blank"
                                               class="w-6 h-6 rounded border border-slate2 overflow-hidden hover:border-jungle-500 transition-colors"
                                               title="View Senior ID">
                                                <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[9px] text-red-400 italic">No IDs uploaded</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $booking->status === 'confirmed' ? 'bg-jungle-100 text-jungle-700' :
                               ($booking->status === 'pending'   ? 'bg-amber-100 text-amber-500' :
                               ($booking->status === 'awaiting_validation' ? 'bg-orange-100 text-orange-600' :
                               'bg-red-100 text-red-500')) }}">
                            {{ $booking->status === 'awaiting_validation' ? 'Validation Required' : ucfirst($booking->status) }}
                        </span>
                        @if($booking->status === 'rejected' && $booking->rejection_reason)
                            <p class="text-[10px] text-red-400 mt-1 italic line-clamp-1" title="{{ $booking->rejection_reason }}">
                                {{ $booking->rejection_reason }}
                            </p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $booking->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Senior Validation Button --}}
                            @if($booking->status === 'awaiting_validation')
                                <a href="{{ route('admin.bookings.validate', $booking->id) }}" 
                                   class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-400 text-white hover:bg-amber-500 transition-colors shadow-sm">
                                    Validate IDs
                                </a>
                            @endif

                            {{-- Quick status update --}}
                            @if($booking->status === 'pending')
                                <form method="POST" action="{{ route('admin.bookings.status', $booking->id) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="px-2 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-400 hover:bg-red-100 transition-colors">
                                        Cancel
                                    </button>
                                </form>
                            @elseif($booking->status === 'confirmed' || $booking->status === 'cancelled' || $booking->status === 'rejected' || ($booking->tourSchedule && \Carbon\Carbon::parse($booking->tourSchedule->date)->isPast()))
                                <form method="POST" action="{{ route('admin.bookings.archive.toggle', $booking->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        Archive
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300 italic">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#d6ece0;">
                            <svg class="w-6 h-6" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No bookings found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
    <div class="px-6 py-4 border-t border-slate2">
        {{ $bookings->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection