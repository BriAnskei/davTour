@extends('layouts.client')

@section('title', 'Booking Details — ' . ($booking->tourSchedule->tour->name ?? 'Tour'))

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">

    {{-- Back --}}
    <a href="{{ route('client.bookings') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-jungle-700 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to My Bookings
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Left: Details --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Status Banner --}}
            <div class="rounded-2xl p-6 shadow-sm border {{ $booking->status === 'confirmed' ? 'border-jungle-100 text-jungle-700' : ($booking->status === 'rejected' ? 'border-red-100 text-red-700' : 'border-amber-100 text-amber-700') }}">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ $booking->status === 'confirmed' ? 'bg-jungle-500 text-white' : ($booking->status === 'rejected' ? 'bg-red-500 text-white' : 'bg-amber-400 text-white') }}">
                        @if($booking->status === 'confirmed')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @elseif($booking->status === 'rejected')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">
                            {{ $booking->status === 'awaiting_validation' ? 'Awaiting Senior Validation' : ucfirst($booking->status) }}
                        </h2>
                        <p class="text-sm opacity-80">
                            @if($booking->status === 'confirmed')
                                Your booking is confirmed and ready for the tour!
                            @elseif($booking->status === 'rejected')
                                This booking was rejected by the admin.
                            @elseif($booking->status === 'pending')
                                Awaiting payment to confirm your reservation.
                            @elseif($booking->status === 'awaiting_validation')
                                Please wait while we verify your Senior Citizen ID.
                            @else
                                This booking is {{ $booking->status }}.
                            @endif
                        </p>
                    </div>
                </div>

                @if($booking->status === 'rejected')
                    @if($booking->rejection_reason)
                        <div class="mt-4 p-4 rounded-xl border border-red-200">
                            <p class="text-xs font-bold uppercase tracking-widest text-red-400 mb-1">Reason for Rejection</p>
                            <p class="text-sm text-red-700 font-medium italic">"{{ $booking->rejection_reason }}"</p>
                        </div>
                    @endif
                    <div class="mt-4">
                        <form method="POST" action="{{ route('client.payment.resume', $booking->id) }}"
                              onsubmit="confirmAction(event, {
                                  title: 'Resubmit Booking?',
                                  description: 'You will be able to update your details and submit the booking again.',
                                  confirmText: 'Resubmit',
                                  variant: 'warning'
                              })">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition-all shadow-md flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Resubmit Booking
                            </button>
                        </form>
                    </div>
                @endif
                
                @if($booking->status === 'pending')
                    <div class="mt-4">
                        <form method="POST" action="{{ route('client.payment.resume', $booking->id) }}">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 text-white text-sm font-bold hover:bg-amber-600 transition-all shadow-md">
                                Proceed to Payment
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Cancel Booking Button --}}
                @if($booking->status !== 'cancelled')
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('client.bookings.cancel', $booking->id) }}"
                              onsubmit="confirmAction(event, {
                                  title: 'Cancel Booking?',
                                  description: 'Are you sure you want to cancel this booking? This action is permanent.',
                                  confirmText: 'Yes, Cancel',
                                  variant: 'danger'
                              })">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-full py-3 rounded-xl border border-red-100 text-red-500 text-sm font-bold hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Cancel Booking
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Tour Summary --}}
            <div class="bg-white rounded-2xl card-shine p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden shrink-0">
                        @if($booking->tourSchedule->tour->images->isNotEmpty())
                            <img src="{{ asset('storage/' . $booking->tourSchedule->tour->images->first()->image) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-jungle-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-jungle-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="1.5"/></svg>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-jungle-700 text-xl">{{ $booking->tourSchedule->tour->name }}</h3>
                        <p class="text-sm text-gray-400 flex items-center gap-1 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $booking->tourSchedule->tour->location }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 py-4 border-y border-gray-100">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Date</p>
                        <p class="text-sm font-bold text-jungle-700">{{ \Carbon\Carbon::parse($booking->tourSchedule->date)->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Persons</p>
                        <p class="text-sm font-bold text-jungle-700">{{ $booking->p_count }} Pax</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Seniors</p>
                        <p class="text-sm font-bold text-amber-500">{{ $booking->senior_count }} included</p>
                    </div>
                </div>
            </div>

            {{-- Senior IDs --}}
            @if($booking->seniorImages->isNotEmpty())
            <div class="bg-white rounded-2xl card-shine p-6">
                <h3 class="text-sm font-bold text-jungle-700 mb-4">Submitted Senior ID Pictures</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($booking->seniorImages as $image)
                        <div class="aspect-[4/3] rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
                            <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- Right: Payment Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl card-shine p-6">
                <h3 class="text-sm font-bold text-jungle-700 mb-4">Pricing Breakdown</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Regular Price</span>
                        <span class="text-gray-700 font-medium">₱{{ number_format($booking->tourSchedule->tour->price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Regular Pax ({{ $booking->p_count - $booking->senior_count }})</span>
                        <span class="text-gray-700 font-medium">₱{{ number_format(($booking->p_count - $booking->senior_count) * $booking->tourSchedule->tour->price, 2) }}</span>
                    </div>
                    @if($booking->senior_count > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Senior Pax ({{ $booking->senior_count }})</span>
                            <span class="text-gray-700 font-medium">₱{{ number_format($booking->senior_count * $booking->tourSchedule->tour->price * 0.8, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-red-500 font-medium italic">
                            <span>Senior Discount</span>
                            <span>-₱{{ number_format($booking->senior_count * $booking->tourSchedule->tour->price * 0.2, 2) }}</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-gray-100 flex justify-between">
                        <span class="font-bold text-jungle-700">Total Amount</span>
                        @php
                            $total = (($booking->p_count - $booking->senior_count) * $booking->tourSchedule->tour->price) + ($booking->senior_count * $booking->tourSchedule->tour->price * 0.8);
                        @endphp
                        <span class="font-display font-bold text-amber-400 text-lg">₱{{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($booking->payments->isNotEmpty())
            <div class="bg-white rounded-2xl card-shine p-6">
                <h3 class="text-sm font-bold text-jungle-700 mb-4">Payment History</h3>
                <div class="space-y-4">
                    @foreach($booking->payments as $payment)
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-xs font-bold text-jungle-700">₱{{ number_format($payment->amount, 2) }}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $payment->created_at->format('M d, Y') }}</p>
                            </div>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-widest {{ $payment->payment_status === 'completed' ? 'text-jungle-500' : 'text-amber-500' }}">
                                {{ $payment->payment_status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-jungle-700 rounded-2xl p-6 text-white text-center">
                <p class="text-xs text-white/60 mb-1">Booking Ref #</p>
                <p class="text-xl font-display font-bold">DT-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>

    </div>
</div>
@endsection