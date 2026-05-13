@extends('layouts.admin')

@section('title', 'Tour Schedules')
@section('page-title', 'Tour Schedules')
@section('page-subtitle', 'Manage all scheduled tour dates and slots')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex gap-2">
        <a href="{{ route('tour_schedules.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 hover:shadow-lg transition-all"
           style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Schedule
        </a>
        <button type="button"
                onclick="openAuditLogModal('schedules')"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-jungle-700 bg-white border border-slate2 transition-all hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            View History
        </button>
    </div>

    <form method="GET" action="{{ route('tour_schedules.index') }}" class="flex items-center gap-2">
        <select name="tour_id" class="px-3 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white text-gray-600 min-w-[180px]">
            <option value="">All Tours</option>
            @foreach($tours ?? [] as $t)
            <option value="{{ $t->id }}" {{ request('tour_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>
        <input type="month" name="month" value="{{ request('month') }}"
               class="px-3 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white text-gray-600">
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-400 text-white text-sm font-semibold hover:bg-amber-500 transition-colors">Filter</button>
        @if(request()->hasAny(['tour_id','month']))
        <a href="{{ route('tour_schedules.index') }}" class="text-xs text-gray-400 hover:text-gray-600">Clear</a>
        @endif
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
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Available Slots</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Booked</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate2">
                @forelse($schedules as $sched)
                @php
                    $bookedCount = $sched->bookings->count() ?? 0;
                    $remaining = $sched->slots - $bookedCount;
                    $isPast = \Carbon\Carbon::parse($sched->date)->isPast();
                @endphp
                <tr class="hover:bg-cream transition-colors">
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
                        <div class="flex items-center gap-2">
                            <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $remaining > 5 ? 'bg-green-400' : 'bg-red-400' }}"
                                     style="width: {{ $sched->slots > 0 ? min(100, ($bookedCount / $sched->slots) * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold {{ $remaining > 0 ? 'text-jungle-700' : 'text-red-500' }}">
                                {{ $remaining }} left
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">of {{ $sched->slots }} total</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-amber-400">{{ $bookedCount }}</span>
                        <span class="text-xs text-gray-400"> bookings</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($isPast)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-400">Past</span>
                        @elseif($remaining === 0)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-500">Full</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-jungle-100 text-jungle-700">Open</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('tour_schedules.edit', $sched->id) }}"
                               class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-400 hover:bg-blue-50 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('tour_schedules.destroy', $sched->id) }}"
                                  onsubmit="return confirm('Delete this schedule?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#f9e3bb;">
                            <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No schedules found.</p>
                        <a href="{{ route('tour_schedules.create') }}" class="text-xs text-amber-400 hover:text-amber-500 font-semibold mt-1 inline-block">Add one now →</a>
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