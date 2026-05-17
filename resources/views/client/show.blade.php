@extends('layouts.client')

@section('title', $tour->name . ' — DavaoTours')

@push('styles')
<style>
    /* Carousel Styles */
    .carousel-container { position: relative; overflow: hidden; }
    .carousel-track { display: flex; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform; }
    .carousel-slide { min-width: 100%; height: 100%; flex-shrink: 0; }
    
    /* Modal / Lightbox */
    #lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 100;
        background: rgba(0,0,0,0.95);
        backdrop-filter: blur(8px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    #lightbox.open { display: flex; opacity: 1; }

    /* Gallery */
    .thumb { transition: all .2s ease; }
    .thumb.active { border-color: #c9872a; }

    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10">

    {{-- Back link --}}
    <a href="{{ route('client.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-jungle-700 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Tours
    </a>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- Left: Images + Description --}}
        <div class="xl:col-span-2 space-y-6 fade-up">

            {{-- Image Gallery / Carousel --}}
            <div class="bg-white rounded-2xl card-shine overflow-hidden group/gallery">
                <div class="carousel-container relative h-80 sm:h-96 overflow-hidden bg-jungle-100">
                    
                    {{-- Main Track --}}
                    <div id="carousel-track" class="carousel-track h-full">
                        @forelse($tour->images as $img)
                        <div class="carousel-slide cursor-zoom-in" onclick="openLightbox('{{ asset('storage/' . $img->image) }}')">
                            <img src="{{ asset('storage/' . $img->image) }}" 
                                 alt="{{ $tour->name }}"
                                 class="w-full h-full object-cover">
                        </div>
                        @empty
                        <div class="carousel-slide w-full h-full flex items-center justify-center"
                             style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                            <svg class="w-16 h-16 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        @endforelse
                    </div>

                    {{-- Navigation Arrows (Only if multiple) --}}
                    @if($tour->images->count() > 1)
                        <button onclick="moveSlide(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 backdrop-blur text-white flex items-center justify-center opacity-0 group-hover/gallery:opacity-100 transition-opacity hover:bg-white/40 z-20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button onclick="moveSlide(1)" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 backdrop-blur text-white flex items-center justify-center opacity-0 group-hover/gallery:opacity-100 transition-opacity hover:bg-white/40 z-20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        {{-- Dots Indicator --}}
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5 z-20">
                            @foreach($tour->images as $index => $img)
                            <button onclick="goToSlide({{ $index }})" 
                                    class="carousel-dot w-2 h-2 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-white scale-125' : 'bg-white/40' }}"></button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Fullscreen Toggle Label --}}
                    <div class="absolute top-4 right-4 px-3 py-1.5 rounded-lg bg-black/40 backdrop-blur text-[10px] font-bold text-white uppercase tracking-widest pointer-events-none opacity-0 group-hover/gallery:opacity-100 transition-opacity z-20">
                        Click to Expand
                    </div>
                </div>

                {{-- Thumbnails --}}
                @if($tour->images->count() > 1)
                <div class="p-4 flex gap-3 overflow-x-auto scrollbar-hide border-t border-gray-100">
                    @foreach($tour->images as $index => $img)
                    <button onclick="goToSlide({{ $index }})"
                            class="thumb shrink-0 w-20 h-14 rounded-xl overflow-hidden border-2 transition-all {{ $index === 0 ? 'active border-amber-400 scale-105' : 'border-transparent opacity-60 hover:opacity-100' }}">
                        <img src="{{ asset('storage/' . $img->image) }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- About --}}
            <div class="bg-white rounded-2xl card-shine p-6">
                <h2 class="font-display text-jungle-700 font-bold text-lg mb-3">About This Tour</h2>
                <div class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                    {{ $tour->description ?? 'Experience the best of Davao City with this amazing tour.' }}
                </div>
            </div>

            {{-- Available Schedules --}}
            <div class="bg-white rounded-2xl card-shine overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-display text-jungle-700 font-bold text-lg">Available Schedules</h2>
                    <p class="text-gray-400 text-xs mt-0.5">Upcoming dates for this tour</p>
                </div>

                @if($tour->schedules->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach($tour->schedules as $schedule)
                    @php
                        $remaining = $schedule->slots - $schedule->bookings_count;
                        $isFull    = $remaining <= 0;
                    @endphp
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-cream transition-colors">
                        <div class="flex items-center gap-4">
                            {{-- Date block --}}
                            <div class="w-14 h-14 rounded-xl flex flex-col items-center justify-center shrink-0"
                                 style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                                <span class="text-white/70 text-xs font-semibold uppercase">
                                    {{ \Carbon\Carbon::parse($schedule->date)->format('M') }}
                                </span>
                                <span class="text-white font-display font-bold text-xl leading-none">
                                    {{ \Carbon\Carbon::parse($schedule->date)->format('d') }}
                                </span>
                            </div>
                            <div>
                                <p class="font-semibold text-jungle-700 text-sm">
                                    {{ \Carbon\Carbon::parse($schedule->date)->format('l, F d, Y') }}
                                </p>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-xs text-gray-400">
                                        {{ $schedule->slots }} total slots
                                    </span>
                                    <span class="text-xs font-semibold {{ $isFull ? 'text-red-500' : ($remaining <= 5 ? 'text-orange-400' : 'text-jungle-500') }}">
                                        {{ $isFull ? 'Fully booked' : $remaining . ' slots left' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Book button --}}
                        @if($isFull)
                            <span class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 text-gray-400 cursor-not-allowed">
                                Full
                            </span>
                        @elseif(Auth::check() && Auth::user()->role === 'user')
                            <a href="{{ route('client.booking.create', ['schedule_id' => $schedule->id]) }}"
                               class="px-4 py-2 rounded-xl text-xs font-bold text-white hover:opacity-90 transition-all hover:shadow-md"
                               style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
                                Book Now
                            </a>
                        @else
                            <button onclick="toggleAuthDropdown()"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-white hover:opacity-90 transition-all"
                                    style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                                Sign In to Book
                            </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#f9e3bb;">
                        <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-gray-400 text-sm font-medium">No upcoming schedules available.</p>
                    <p class="text-gray-300 text-xs mt-1">Check back later for new dates.</p>
                </div>
                @endif
            </div>

        </div>

        {{-- Right: Tour Summary Card --}}
        <div class="fade-up" style="animation-delay:.1s;">
            <div class="bg-white rounded-2xl card-shine p-6 sticky top-24">

                <h1 class="font-display text-jungle-700 text-2xl font-bold leading-tight mb-2">
                    {{ $tour->name }}
                </h1>

                <div class="flex items-center gap-1.5 text-gray-400 text-sm mb-5">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ $tour->location }}
                </div>

                <div class="pt-4 border-t border-gray-100 mb-5">
                    <p class="text-xs text-gray-400 mb-1">Price per person</p>
                    <p class="font-display text-3xl font-bold text-amber-400">₱{{ number_format($tour->price, 2) }}</p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">Available Dates</span>
                        <span class="font-semibold text-jungle-700">{{ $tour->schedules->count() }} upcoming</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">Photos</span>
                        <span class="font-semibold text-jungle-700">{{ $tour->images->count() }} photos</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-500">Status</span>
                        <span class="px-2.5 py-1 text-xs font-bold text-green-600">
                            {{ ucfirst($tour->status) }}
                        </span>
                    </div>
                </div>

                @guest
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <p class="text-xs text-gray-400 text-center mb-3">Sign in to book a schedule</p>
                    <button onclick="toggleAuthDropdown()"
                            class="w-full py-3 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-all"
                            style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                        Sign In to Book
                    </button>
                </div>
                @endguest

            </div>
        </div>

    </div>
</div>

{{-- ===== LIGHTBOX MODAL ===== --}}
<div id="lightbox" onclick="closeLightbox()" class="items-center justify-center p-4">
    <button onclick="closeLightbox()" class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="lightbox-img" class="max-w-full max-h-full rounded-2xl shadow-2xl transition-transform duration-300 scale-95" src="" alt="Full View">
</div>
@endsection

@push('scripts')
<script>
// Carousel Logic
let currentSlide = 0;
const totalSlides = {{ $tour->images->count() }};
const track = document.getElementById('carousel-track');
const dots = document.querySelectorAll('.carousel-dot');
const thumbs = document.querySelectorAll('.thumb');
const container = document.querySelector('.carousel-container');

let autoPlayInterval;
const autoPlayDelay = 5000;

function startAutoPlay() {
    if (totalSlides > 1 && !autoPlayInterval) {
        autoPlayInterval = setInterval(() => {
            moveSlide(1, false);
        }, autoPlayDelay);
    }
}

function stopAutoPlay() {
    clearInterval(autoPlayInterval);
    autoPlayInterval = null;
}

function updateCarousel() {
    if (!track) return;
    track.style.transform = `translateX(-${currentSlide * 100}%)`;
    
    // Update dots
    dots.forEach((dot, i) => {
        dot.classList.toggle('bg-white', i === currentSlide);
        dot.classList.toggle('scale-125', i === currentSlide);
        dot.classList.toggle('bg-white/40', i !== currentSlide);
    });

    // Update thumbs
    thumbs.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === currentSlide);
        thumb.classList.toggle('border-amber-400', i === currentSlide);
        thumb.classList.toggle('scale-105', i === currentSlide);
        thumb.classList.toggle('opacity-60', i !== currentSlide);
        thumb.classList.toggle('border-transparent', i !== currentSlide);
        if (i === currentSlide) thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });
}

function moveSlide(direction, manual = true) {
    if (manual) stopAutoPlay();
    currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
    updateCarousel();
    if (manual) startAutoPlay();
}

function goToSlide(index) {
    stopAutoPlay();
    currentSlide = index;
    updateCarousel();
    startAutoPlay();
}

// Initial AutoPlay
startAutoPlay();

// Swipe Support
let touchStartX = 0;
let touchEndX = 0;

if (track) {
    track.addEventListener('touchstart', e => touchStartX = e.changedTouches[0].screenX, {passive: true});
    track.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, {passive: true});
}

function handleSwipe() {
    if (touchStartX - touchEndX > 50) moveSlide(1);
    if (touchEndX - touchStartX > 50) moveSlide(-1);
}

// Lightbox Logic
function openLightbox(src) {
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    img.src = src;
    lightbox.classList.add('open');
    setTimeout(() => img.classList.remove('scale-95'), 10);
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    img.classList.add('scale-95');
    lightbox.classList.remove('open');
}

// Keyboard nav
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') moveSlide(-1);
    if (e.key === 'ArrowRight') moveSlide(1);
    if (e.key === 'Escape') closeLightbox();
});
</script>
@endpush
