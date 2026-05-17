@extends('layouts.client')

@section('title', 'My Bookings — DavaoTours')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">

    <div class="mb-8 fade-up">
        <h1 class="font-display text-jungle-700 text-3xl font-bold">My Bookings</h1>
        <p class="text-gray-400 text-sm mt-1">Your tour reservation history</p>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-4 mb-6 fade-up">
        <a href="{{ route('client.bookings', ['tab' => 'upcoming']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $tab === 'upcoming' ? 'bg-jungle-700 text-white' : 'bg-white text-gray-400 border border-gray-100 hover:bg-gray-50' }}">
            Upcoming Bookings
        </a>
        <a href="{{ route('client.bookings', ['tab' => 'past']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $tab === 'past' ? 'bg-jungle-700 text-white' : 'bg-white text-gray-400 border border-gray-100 hover:bg-gray-50' }}">
            Past History
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('client.bookings') }}" class="mb-8 fade-up flex flex-wrap gap-4 items-end">
        <input type="hidden" name="tab" value="{{ $tab }}">
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Search Tour</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="e.g. Samal Island"
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-100 text-sm focus:outline-none focus:border-jungle-500 bg-white shadow-sm">
            </div>
        </div>

        <div class="w-full sm:w-48">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Status</label>
            <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-gray-100 text-sm focus:outline-none focus:border-jungle-500 bg-white text-gray-600 shadow-sm">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="awaiting_validation" {{ request('status') === 'awaiting_validation' ? 'selected' : '' }}>Awaiting Validation</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-jungle-700 text-white text-sm font-bold hover:bg-jungle-800 transition-all shadow-md">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('client.bookings', ['tab' => $tab]) }}" class="px-4 py-2.5 rounded-xl border border-gray-100 text-gray-400 hover:bg-gray-50 transition-all text-sm font-bold flex items-center justify-center bg-white shadow-sm">
                    Clear
                </a>
            @endif
        </div>
    </form>

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

    @if($bookings->count() > 0)
    <div class="space-y-4">
        @foreach($bookings as $index => $booking)
        @php
            $tour     = $booking->tourSchedule->tour ?? null;
            $schedule = $booking->tourSchedule ?? null;
        @endphp
        <div class="bg-white rounded-2xl card-shine overflow-hidden fade-up"
             style="animation-delay: {{ $index * 0.07 }}s;">
            <div class="flex flex-col sm:flex-row">

                {{-- Tour Image --}}
                <div class="sm:w-40 h-32 sm:h-auto shrink-0 overflow-hidden bg-jungle-100">
                    @if($tour && $tour->images->isNotEmpty())
                        <img src="{{ asset('storage/' . $tour->images->first()->image) }}"
                             class="w-full h-full object-cover" alt="{{ $tour->name }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center"
                             style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                            <svg class="w-10 h-10 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Booking Info --}}
                <div class="flex-1 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-display font-bold text-jungle-700 text-base">
                                {{ $tour->name ?? 'Tour Unavailable' }}
                            </h3>
                        </div>

                        <div class="flex items-center gap-1.5 text-gray-400 text-xs mb-3">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $tour->location ?? '—' }}
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <p class="text-gray-400 mb-0.5">Schedule Date</p>
                                <p class="font-semibold text-jungle-700">
                                    {{ $schedule ? \Carbon\Carbon::parse($schedule->date)->format('M d, Y') : '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-400 mb-0.5">Persons</p>
                                <p class="font-semibold text-jungle-700">{{ $booking->p_count }} pax</p>
                            </div>
                            <div>
                                <p class="text-gray-400 mb-0.5">Total</p>
                                <p class="font-display font-bold text-amber-400 text-sm">
                                    @php
                                        $price = $tour->price ?? 0;
                                        $regularCount = $booking->p_count - $booking->senior_count;
                                        $total = ($regularCount * $price) + ($booking->senior_count * $price * 0.8);
                                    @endphp
                                    ₱{{ number_format($total, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Status + Action --}}
                    <div class="flex flex-col items-end gap-3 shrink-0">
                        <span class="px-3 py-1.5 text-xs font-bold
                            {{ $booking->status === 'confirmed'  ? 'text-jungle-700'  :
                               ($booking->status === 'pending'   ? 'text-amber-500'    :
                               ($booking->status === 'awaiting_validation' ? 'text-orange-500' :
                               'text-red-500')) }}">
                            {{ $booking->status === 'awaiting_validation' ? 'Awaiting Validation' : ucfirst($booking->status) }}
                        </span>

                        <div class="flex items-center gap-3">
                            {{-- Details Button --}}
                            <a href="{{ route('client.bookings.show', $booking->id) }}"
                               class="px-4 py-2 rounded-xl text-xs font-bold border border-gray-100 hover:bg-gray-50 transition-all">
                                View Details
                            </a>

                            {{-- Resume Payment button (pending only) --}}
                            @if($booking->status === 'pending')
                            <form method="POST" action="{{ route('client.payment.resume', $booking->id) }}">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl text-xs font-bold text-white transition-all hover:shadow-lg"
                                        style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
                                    Proceed to Payment
                                </button>
                            </form>
                            @endif

                            {{-- Resubmit button (rejected only) --}}
                            @if($booking->status === 'rejected')
                            <form method="POST" action="{{ route('client.payment.resume', $booking->id) }}"
                                  onsubmit="confirmAction(event, {
                                      title: 'Resubmit Booking?',
                                      description: 'This will allow you to update your booking details and resubmit for approval.',
                                      confirmText: 'Resubmit',
                                      variant: 'warning'
                                  })">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl text-xs font-bold text-white transition-all bg-red-600 hover:bg-red-700 hover:shadow-lg">
                                    Resubmit Booking
                                </button>
                            </form>
                            @endif

                            @if($booking->status === 'awaiting_validation')
                                <p class="text-[10px] text-gray-400 italic">Admin is verifying your Senior ID...</p>
                            @endif
                            
                            {{-- Cancel button --}}
                            @if($booking->status !== 'cancelled')
                            <form method="POST" action="{{ route('client.bookings.cancel', $booking->id) }}"
                                  onsubmit="confirmAction(event, {
                                      title: 'Cancel Booking?',
                                      description: 'Are you sure you want to cancel your reservation for {{ $tour->name ?? 'this tour' }}? This action cannot be reversed.',
                                      confirmText: 'Yes, Cancel',
                                      variant: 'danger'
                                  })">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="text-xs text-red-400 hover:text-red-600 font-semibold transition-colors">
                                    Cancel
                                </button>
                            </form>
                            @endif
                        </div>

                        <p class="text-xs text-gray-400">
                            Booked {{ $booking->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-8">{{ $bookings->links() }}</div>

    @else
    {{-- Empty state --}}
    <div class="bg-white rounded-2xl card-shine py-20 text-center fade-up">
        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#d6ece0;">
            <svg class="w-8 h-8" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h3 class="font-display text-jungle-700 text-xl font-bold mb-2">No bookings yet</h3>
        <p class="text-gray-400 text-sm mb-6">You haven't booked any tours yet. Start exploring!</p>
        <a href="{{ route('client.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
           style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
            Browse Tours →
        </a>
    </div>
    @endif

</div>
@endsection