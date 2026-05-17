@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your tour operations')

@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

    {{-- Total Tours --}}
    <div class="bg-white rounded-2xl p-5 card-shine border border-slate2 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-10" style="background:#1a3a2a; transform:translate(30%,-30%);"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#d6ece0;">
                <svg class="w-5 h-5" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5" style="color:#1a3a2a;">Tours</span>
        </div>
        <p class="text-3xl font-display font-bold text-jungle-700">{{ $totalTours ?? 0 }}</p>
        <p class="text-sm text-gray-400 mt-1">Total Tours</p>
    </div>

    {{-- Active Schedules --}}
    <div class="bg-white rounded-2xl p-5 card-shine border border-slate2 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-10" style="background:#c9872a; transform:translate(30%,-30%);"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f9e3bb;">
                <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5" style="color:#a86d1e;">Schedules</span>
        </div>
        <p class="text-3xl font-display font-bold text-amber-400">{{ $totalSchedules ?? 0 }}</p>
        <p class="text-sm text-gray-400 mt-1">Active Schedules</p>
    </div>

    {{-- Total Bookings --}}
    <div class="bg-white rounded-2xl p-5 card-shine border border-slate2 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-10" style="background:#8b5e3c; transform:translate(30%,-30%);"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f5ede6;">
                <svg class="w-5 h-5 text-earth-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5" style="color:#8b5e3c;">Bookings</span>
        </div>
        <p class="text-3xl font-display font-bold" style="color:#8b5e3c;">{{ $totalBookings ?? 0 }}</p>
        <p class="text-sm text-gray-400 mt-1">Total Bookings</p>
    </div>

    {{-- Revenue --}}
    <div class="rounded-2xl p-5 card-shine relative overflow-hidden text-white" style="background: linear-gradient(135deg, #1a3a2a 0%, #2d6a4f 100%);">
        <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-20" style="background:#c9872a; transform:translate(30%,-30%);"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-white/10">
                <svg class="w-5 h-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5 text-amber-200">Revenue</span>
        </div>
        <p class="text-3xl font-display font-bold text-amber-300">₱{{ number_format($totalRevenue ?? 0, 2) }}</p>
        <p class="text-sm text-white/60 mt-1">Total Confirmed</p>
    </div>

</div>

{{-- Recent Bookings + Quick Actions --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Recent Bookings Table --}}
    <div class="xl:col-span-2 bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate2 flex items-center justify-between">
            <h2 class="font-display text-jungle-700 font-bold text-base">Recent Bookings</h2>
            <a href="{{ route('admin.bookings.index') }}" class="text-xs text-amber-400 hover:text-amber-500 font-semibold">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-cream border-b border-slate2">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Guest</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tour</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate2">
                    @forelse($recentBookings ?? [] as $booking)
                    <tr class="hover:bg-cream transition-colors">
                        <td class="px-6 py-3 font-medium text-jungle-700">{{ $booking->user->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $booking->tourSchedule->tour->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $booking->tourSchedule->date ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @php $s = $booking->status; @endphp
                            <span class="px-2 py-0.5 text-xs font-semibold
                                {{ $s === 'confirmed' ? 'text-jungle-700' : ($s === 'pending' ? 'text-amber-500' : 'text-red-500') }}">
                                {{ ucfirst($s) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">No bookings yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">
        <h2 class="font-display text-jungle-700 font-bold text-base mb-5">Quick Actions</h2>
        <div class="space-y-3">
            <a href="{{ route('admin.tours.create') }}"
               class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 hover:translate-x-0.5"
               style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add New Tour
            </a>
            <a href="{{ route('admin.tour_schedules.create') }}"
               class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 hover:translate-x-0.5"
               style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                New Schedule
            </a>
            <a href="{{ route('admin.payments.index') }}"
               class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:translate-x-0.5"
               style="background:#f5ede6; color:#8b5e3c;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                View Payments
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:translate-x-0.5"
               style="background:#f0f7f3; color:#1a3a2a;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Manage Users
            </a>
        </div>

        {{-- Mini stat --}}
        <div class="mt-6 pt-5 border-t border-slate2">
            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-3">Today's Summary</p>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">New bookings</span>
                    <span class="font-semibold text-jungle-700">{{ $todayBookings ?? 0 }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pending payments</span>
                    <span class="font-semibold text-amber-400">{{ $pendingPayments ?? 0 }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Upcoming tours</span>
                    <span class="font-semibold text-earth-500">{{ $upcomingTours ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection