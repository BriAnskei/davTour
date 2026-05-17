@extends('layouts.admin')

@section('title', 'New Schedule')
@section('page-title', 'New Tour Schedule')
@section('page-subtitle', 'Add a new date and slot for a tour')

@section('content')

<div class="max-w-xl">
    <form method="POST" action="{{ route('admin.tour_schedules.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6 space-y-5">
            <h2 class="font-display text-jungle-700 font-bold text-base">Schedule Details</h2>

            {{-- Tour --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1.5">Tour <span class="text-red-400">*</span></label>
                <select name="tour_id"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white @error('tour_id') border-red-400 @enderror">
                    <option value="">— Select a tour —</option>
                    @foreach($tours as $tour)
                    <option value="{{ $tour->id }}" {{ old('tour_id', request('tour_id')) == $tour->id ? 'selected' : '' }}>
                        {{ $tour->name }}
                    </option>
                    @endforeach
                </select>
                @error('tour_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1.5">Schedule Date <span class="text-red-400">*</span></label>
                <input type="date" name="date" value="{{ old('date') }}" min="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white @error('date') border-red-400 @enderror">
                @error('date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Slots --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1.5">Available Slots <span class="text-red-400">*</span></label>
                <input type="number" name="slots" value="{{ old('slots', 10) }}" min="1"
                       placeholder="e.g. 20"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 @error('slots') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1">Maximum number of people that can book this schedule.</p>
                @error('slots')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all hover:shadow-lg"
                    style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                Create Schedule
            </button>
            <a href="{{ route('admin.tour_schedules.index') }}"
               class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-500 bg-white border border-slate2 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection