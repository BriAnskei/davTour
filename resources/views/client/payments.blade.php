@extends('layouts.client')

@section('title', 'My Payments — DavaoTours')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">

    <div class="mb-8 fade-up">
        <h1 class="font-display text-jungle-700 text-3xl font-bold">My Payments</h1>
        <p class="text-gray-400 text-sm mt-1">History of your tour transactions</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-xl bg-jungle-50 border border-jungle-100 text-jungle-700 text-sm flex items-center gap-2 fade-up">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm flex items-center gap-2 fade-up">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    @if($payments->count() > 0)
    <div class="bg-white rounded-2xl card-shine overflow-hidden fade-up">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Transaction #</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tour</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($payments as $payment)
                    @php
                        $booking  = $payment->booking;
                        $tour     = $booking->tourSchedule->tour ?? null;
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-xs text-gray-400 font-mono">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-jungle-700 text-xs">{{ $tour->name ?? 'Deleted Tour' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $booking->tourSchedule ? \Carbon\Carbon::parse($booking->tourSchedule->date)->format('M d, Y') : '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-display font-bold text-amber-400 text-sm">₱{{ number_format($payment->amount, 2) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold
                                {{ $payment->payment_status === 'completed' ? 'bg-jungle-50 text-jungle-700' : 'bg-amber-50 text-amber-500' }}">
                                {{ strtoupper($payment->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            {{ $payment->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-8">{{ $payments->links() }}</div>

    @else
    {{-- Empty state --}}
    <div class="bg-white rounded-2xl card-shine py-20 text-center fade-up">
        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#f9e3bb;">
            <svg class="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h3 class="font-display text-jungle-700 text-xl font-bold mb-2">No payments yet</h3>
        <p class="text-gray-400 text-sm mb-6">You haven't made any payments yet. Book a tour to get started!</p>
        <a href="{{ route('client.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
           style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
            Explore Tours →
        </a>
    </div>
    @endif

</div>
@endsection
