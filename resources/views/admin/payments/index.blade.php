@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payments')
@section('page-subtitle', 'View all client tour payments')

@section('content')

{{-- Revenue Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-2xl card-shine border border-slate2 p-5 relative overflow-hidden">
        <div class="absolute inset-0 opacity-5" style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);"></div>
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-2">Total Revenue</p>
        <p class="font-display text-2xl font-bold text-jungle-700">₱{{ number_format($totalRevenue ?? 0, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">All completed payments</p>
    </div>
    <div class="bg-white rounded-2xl card-shine border border-slate2 p-5">
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-2">Pending Payments</p>
        <p class="font-display text-2xl font-bold text-amber-400">{{ $pendingCount ?? 0 }}</p>
        <p class="text-xs text-gray-400 mt-1">Awaiting completion</p>
    </div>
    <div class="rounded-2xl p-5 text-white card-shine" style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
        <p class="text-xs text-white/70 font-semibold uppercase tracking-wider mb-2">Today's Collection</p>
        <p class="font-display text-2xl font-bold">₱{{ number_format($todayRevenue ?? 0, 2) }}</p>
        <p class="text-xs text-white/60 mt-1">{{ now()->format('M d, Y') }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap gap-3 mb-5">
    <select name="tour_id"
            class="px-3 py-2.5 rounded-xl border border-slate2 text-sm bg-white text-gray-600 focus:outline-none focus:border-jungle-500">
        <option value="">All Tours</option>
        @foreach($tours ?? [] as $tour)
            <option value="{{ $tour->id }}" {{ request('tour_id') == $tour->id ? 'selected' : '' }}>
                {{ $tour->name }}
            </option>
        @endforeach
    </select>

    <input type="date" name="date" value="{{ request('date') }}"
           class="px-3 py-2.5 rounded-xl border border-slate2 text-sm bg-white text-gray-600 focus:outline-none focus:border-jungle-500">

    <select name="status"
            class="px-3 py-2.5 rounded-xl border border-slate2 text-sm bg-white text-gray-600 focus:outline-none focus:border-jungle-500">
        <option value="">All Status</option>
        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
        <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Failed</option>
    </select>

    <button type="submit"
            class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-colors"
            style="background:#1a3a2a;">
        Filter
    </button>

    @if(request()->hasAny(['tour_id', 'date', 'status']))
        <a href="{{ route('payments.index') }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-500 bg-white border border-slate2 hover:bg-gray-50 transition-colors">
            Clear
        </a>
    @endif
</form>

{{-- Payments Table --}}
<div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f5f1eb;" class="border-b border-slate2">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tour</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Schedule Date</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Persons</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date Paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate2">
                @forelse($payments as $payment)
                @php
                    $booking  = $payment->booking;
                    $user     = $booking->user ?? null;
                    $schedule = $booking->tourSchedule ?? null;
                    $tour     = $schedule->tour ?? null;
                @endphp
                <tr class="hover:bg-cream transition-colors">
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $payment->id }}</td>

                    {{-- Client --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                 style="background:#2d6a4f;">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-jungle-700 text-xs">{{ $user->name ?? '—' }}</p>
                                <p class="text-gray-400 text-xs">{{ $user->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Tour --}}
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-700 text-xs">{{ $tour->name ?? '—' }}</p>
                        <p class="text-gray-400 text-xs">{{ $tour->location ?? '' }}</p>
                    </td>

                    {{-- Schedule Date --}}
                    <td class="px-6 py-4">
                        @if($schedule && $schedule->date)
                            <p class="font-medium text-gray-700 text-xs">
                                {{ \Carbon\Carbon::parse($schedule->date)->format('M d, Y') }}
                            </p>
                            <p class="text-gray-400 text-xs">
                                {{ \Carbon\Carbon::parse($schedule->date)->format('l') }}
                            </p>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Persons --}}
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-jungle-50 text-jungle-700">
                            {{ $booking->p_count ?? '—' }} pax
                        </span>
                    </td>

                    {{-- Amount --}}
                    <td class="px-6 py-4">
                        <span class="font-display font-bold text-amber-400 text-sm">
                            ₱{{ number_format($payment->amount, 2) }}
                        </span>
                    </td>

                    {{-- Payment Status --}}
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $payment->payment_status === 'completed' ? 'bg-jungle-100 text-jungle-700'  :
                               ($payment->payment_status === 'pending'   ? 'bg-amber-100 text-amber-500'   :
                               'bg-red-100 text-red-500') }}">
                            {{ ucfirst($payment->payment_status) }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="px-6 py-4 text-gray-400 text-xs">
                        {{ $payment->created_at->format('M d, Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center"
                             style="background:#f9e3bb;">
                            <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No payments found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-slate2">
        {{ $payments->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection