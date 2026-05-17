@extends('layouts.admin')

@section('title', 'Edit Schedule')
@section('page-title', 'Edit Schedule')
@section('page-subtitle', 'Update schedule date or available slots')

@section('content')

<div class="max-w-xl">
    <form method="POST" action="{{ route('admin.tour_schedules.update', $schedule->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6 space-y-5">
            <h2 class="font-display text-jungle-700 font-bold text-base">Schedule Details</h2>

            {{-- Tour (read-only in edit) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1.5">Tour</label>
                <div class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm bg-gray-50 text-gray-500">
                    {{ $schedule->tour->name ?? '—' }}
                </div>
                <input type="hidden" name="tour_id" value="{{ $schedule->tour_id }}">
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1.5">Schedule Date <span class="text-red-400">*</span></label>
                <input type="date" name="date" value="{{ old('date', $schedule->date) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 @error('date') border-red-400 @enderror">
                @error('date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Slots --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1.5">Available Slots <span class="text-red-400">*</span></label>
                <input type="number" name="slots" value="{{ old('slots', $schedule->slots) }}" min="1"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 @error('slots') border-red-400 @enderror">
                @php $booked = $schedule->bookings->count() ?? 0; @endphp
                <p class="text-xs text-gray-400 mt-1">Currently {{ $booked }} booking(s) on this schedule.</p>
                @error('slots')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
                    style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                Save Changes
            </button>
            <a href="{{ route('admin.tour_schedules.index') }}"
               class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-500 bg-white border border-slate2 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection