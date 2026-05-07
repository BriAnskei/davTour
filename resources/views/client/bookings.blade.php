<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings — DavaoTours</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        jungle: { DEFAULT: '#1a3a2a', 50: '#f0f7f3', 100: '#d6ece0', 500: '#2d6a4f', 700: '#1a3a2a' },
                        amber:  { DEFAULT: '#c9872a', 100: '#f9e3bb', 400: '#c9872a' },
                        cream:  { DEFAULT: '#faf6f0' },
                    },
                    fontFamily: {
                        display: ['Playfair Display', 'Georgia', 'serif'],
                        body:    ['DM Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #faf6f0; }
        .card-shine { box-shadow: 0 1px 3px rgba(26,58,42,.08), 0 4px 16px rgba(26,58,42,.06); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp .4s ease both; }
    </style>
</head>
<body>

{{-- Navbar --}}
<nav class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-100"
     style="box-shadow:0 1px 8px rgba(26,58,42,.07);">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('client.index') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#c9872a;">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                </svg>
            </div>
            <span class="font-display font-bold text-jungle-700 text-lg">DavaoTours</span>
        </a>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500 hidden sm:block">
                Hi, <strong class="text-jungle-700">{{ Auth::user()->name }}</strong>
            </span>
            <a href="{{ route('client.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors">
                Browse Tours
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-6 py-10">

    <div class="mb-8 fade-up">
        <h1 class="font-display text-jungle-700 text-3xl font-bold">My Bookings</h1>
        <p class="text-gray-400 text-sm mt-1">Your tour reservation history</p>
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
                                    ₱{{ $tour ? number_format($tour->price * $booking->p_count, 2) : '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Status + Action --}}
                    <div class="flex flex-col items-end gap-3 shrink-0">
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold
                            {{ $booking->status === 'confirmed'  ? 'bg-jungle-100 text-jungle-700'  :
                               ($booking->status === 'pending'   ? 'bg-amber-100 text-amber-500'    :
                               'bg-red-100 text-red-500') }}">
                            {{ ucfirst($booking->status) }}
                        </span>

                        {{-- Cancel button (pending only) --}}
                        @if($booking->status === 'pending')
                        <form method="POST" action="{{ route('client.bookings.cancel', $booking->id) }}"
                              onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs text-red-400 hover:text-red-600 font-semibold transition-colors">
                                Cancel Booking
                            </button>
                        </form>
                        @endif

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

<footer class="mt-16 py-8 border-t border-gray-200 text-center text-xs text-gray-400">
    © {{ date('Y') }} DavaoTours — Proudly showcasing Davao City, Philippines 🇵🇭
</footer>

</body>
</html>