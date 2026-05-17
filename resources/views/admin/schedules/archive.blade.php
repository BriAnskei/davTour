@extends('layouts.admin')

@section('title', 'Archived Schedules')
@section('page-title', 'Schedule Archive')
@section('page-subtitle', 'View and restore archived tour schedules')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <a href="{{ route('admin.tour_schedules.index') }}" class="text-xs font-semibold text-gray-400 hover:text-jungle-700 flex items-center gap-1 transition-colors">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Active Schedules
    </a>

    <form method="GET" action="{{ route('admin.tour_schedules.archive') }}" class="flex items-center gap-2">
        <select name="tour_id" class="px-3 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white text-gray-600 min-w-[180px]">
            <option value="">All Tours</option>
            @foreach($tours ?? [] as $t)
            <option value="{{ $t->id }}" {{ request('tour_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-400 text-white text-sm font-semibold hover:bg-amber-500 transition-colors">Filter</button>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f5f1eb;" class="border-b border-slate2">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tour</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Booked</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Archived On</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate2">
                @forelse($schedules as $sched)
                @php
                    $bookedCount = $sched->bookings->count() ?? 0;
                @endphp
                <tr class="hover:bg-cream transition-colors grayscale opacity-80">
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-semibold text-jungle-700">{{ $sched->tour->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $sched->tour->location ?? '' }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($sched->date)->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($sched->date)->format('l') }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-amber-400">{{ $bookedCount }}</span>
                        <span class="text-xs text-gray-400"> bookings</span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $sched->updated_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end">
                            <form method="POST" action="{{ route('admin.tour_schedules.archive.toggle', $sched->id) }}"
                                  onsubmit="confirmAction(event, {
                                      title: 'Restore Schedule?',
                                      description: 'Move this schedule back to the active list?',
                                      confirmText: 'Yes, Restore',
                                      variant: 'warning'
                                  })">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-jungle-700 text-white hover:opacity-90 transition-colors shadow-sm">
                                    Restore Schedule
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#f3f4f6;">
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No archived schedules found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($schedules->hasPages())
    <div class="px-6 py-4 border-t border-slate2">
        {{ $schedules->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
