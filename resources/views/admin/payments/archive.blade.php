@extends('layouts.admin')

@section('title', 'Archived Payments')
@section('page-title', 'Payment Archive')
@section('page-subtitle', 'View and restore archived payment records')

@section('content')

{{-- Header Actions --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <a href="{{ route('admin.payments.index') }}" class="text-xs font-semibold text-gray-400 hover:text-jungle-700 flex items-center gap-1 transition-colors">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Active Payments
    </a>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.payments.archive') }}" class="flex flex-wrap gap-3">
        <select name="tour_id" class="px-3 py-2.5 rounded-xl border border-slate2 text-sm bg-white text-gray-600 focus:outline-none focus:border-jungle-500">
            <option value="">All Tours</option>
            @foreach($tours ?? [] as $tour)
                <option value="{{ $tour->id }}" {{ request('tour_id') == $tour->id ? 'selected' : '' }}>{{ $tour->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-400 text-white text-sm font-semibold hover:bg-amber-500 transition-colors">Filter</button>
    </form>
</div>

{{-- Payments Table --}}
<div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f5f1eb;" class="border-b border-slate2">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">#</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tour</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Archived On</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate2">
                @forelse($payments as $payment)
                <tr class="hover:bg-cream transition-colors grayscale opacity-80">
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $payment->id }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-jungle-700 text-xs">{{ $payment->booking->user->name ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-700 text-xs">{{ $payment->booking->tourSchedule->tour->name ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-display font-bold text-amber-400 text-sm">₱{{ number_format($payment->amount, 2) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">{{ ucfirst($payment->payment_status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $payment->updated_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('admin.payments.archive.toggle', $payment->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-jungle-700 text-white hover:opacity-90 transition-colors shadow-sm">
                                    Restore
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.destroy', $payment->id) }}" onsubmit="return confirm('Permanently delete this payment record? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 transition-colors" title="Delete Permanently">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">No archived payments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-slate2">{{ $payments->withQueryString()->links() }}</div>
    @endif
</div>
@endsection