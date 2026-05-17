@extends('layouts.admin')

@section('title', 'Archived Bookings')
@section('page-title', 'Booking Archive')
@section('page-subtitle', 'View and manage archived tour reservations')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <a href="{{ route('admin.bookings.index') }}" class="text-xs font-semibold text-gray-400 hover:text-jungle-700 flex items-center gap-1 transition-colors">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Active Bookings
    </a>

    <form method="GET" action="{{ route('admin.bookings.archive') }}" class="flex gap-2">
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
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Archived On</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate2">
                @forelse($bookings as $booking)
                <tr class="hover:bg-cream transition-colors grayscale opacity-80">
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
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $booking->status === 'confirmed' ? 'bg-jungle-100 text-jungle-700' :
                               ($booking->status === 'pending'   ? 'bg-amber-100 text-amber-500' :
                               ($booking->status === 'awaiting_validation' ? 'bg-orange-100 text-orange-600' :
                               'bg-red-100 text-red-500')) }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $booking->updated_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end">
                            <form method="POST" action="{{ route('admin.bookings.archive.toggle', $booking->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-jungle-700 text-white hover:opacity-90 transition-colors shadow-sm">
                                    Restore Booking
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#f3f4f6;">
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No archived bookings found.</p>
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
