@extends('layouts.client')

@section('title', 'Explore Davao Tours')

@section('content')
{{-- ===== HERO ===== --}}
<section class="relative overflow-hidden py-32 px-6 min-h-[500px] flex items-center justify-center">
    
    {{-- Carousel Background --}}
    <div class="absolute inset-0 z-0">
        @if($carouselImages->isNotEmpty())
            <div id="hero-carousel" class="relative w-full h-full">
                @foreach($carouselImages as $index => $image)
                    <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
                         style="background-image: url('{{ $image }}'); background-size: cover; background-position: center;">
                        {{-- Dark Overlay --}}
                        <div class="absolute inset-0 bg-black/50"></div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="absolute inset-0" style="background: linear-gradient(135deg,#122a1e 0%,#1a3a2a 60%,#2d6a4f 100%);">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>
        @endif
    </div>

    {{-- Pattern Overlay --}}
    <div class="absolute inset-0 opacity-10 pointer-events-none z-10"
         style="background-image:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23c9872a\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    
    <div class="relative max-w-4xl mx-auto text-center z-20">
        {{-- Text Content --}}
        <p class="text-amber-300 text-xs font-semibold uppercase tracking-widest mb-3">Davao City, Philippines</p>
        <h1 class="font-display text-white text-4xl sm:text-5xl font-bold leading-tight mb-4">
            Discover the Pearl<br>of the South
        </h1>
        <p class="text-white/80 text-base mb-10 max-w-xl mx-auto drop-shadow-md">
            From Mt. Apo's summit to the shores of Samal Island — explore Davao's most breathtaking destinations.
        </p>

        <form method="GET" action="{{ route('client.index') }}" class="flex gap-2 max-w-md mx-auto">
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search tours or locations..."
                       class="w-full pl-11 pr-4 py-3.5 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-300 bg-white/95">
            </div>
            <button type="submit"
                    class="px-5 py-3.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-all shadow-lg"
                    style="background:#c9872a;">Search</button>
        </form>
    </div>
</section>

{{-- Flash --}}
@if(session('success'))
<div class="max-w-7xl mx-auto px-6 mt-5">
    <div class="px-4 py-3 rounded-xl bg-jungle-50 border border-jungle-100 text-jungle-700 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
</div>
@endif

{{-- ===== TOURS GRID ===== --}}
<section class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="font-display text-jungle-700 text-2xl font-bold">
                {{ request('search') ? 'Search Results' : 'All Tours' }}
            </h2>
            <p class="text-gray-400 text-sm mt-0.5">{{ $tours->total() }} tour(s) available</p>
        </div>
        @if(request('search'))
        <a href="{{ route('client.index') }}" class="text-sm text-amber-400 hover:text-amber-500 font-semibold">Clear ×</a>
        @endif
    </div>

    @if($tours->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tours as $tour)
        <div class="tour-card bg-white rounded-2xl card-shine overflow-hidden group hover:shadow-xl transition-all duration-300">
            <div class="relative h-52 overflow-hidden">
                @if($tour->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $tour->images->first()->image) }}"
                         alt="{{ $tour->name }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="w-full h-full flex items-center justify-center"
                         style="background:linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                        <svg class="w-14 h-14 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                @endif
                @if($tour->images->count() > 1)
                <div class="absolute bottom-3 left-3">
                    <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-black/40 text-white backdrop-blur-sm">
                        📷 {{ $tour->images->count() }}
                    </span>
                </div>
                @endif
            </div>
            <div class="p-5">
                <div class="flex items-center gap-1.5 text-gray-400 text-xs mb-2">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ $tour->location }}
                </div>
                <h3 class="font-display font-bold text-jungle-700 text-lg leading-tight mb-2">{{ $tour->name }}</h3>
                <p class="text-gray-500 text-xs line-clamp-2 leading-relaxed mb-4">
                    {{ $tour->description ?? 'An amazing tour experience awaits you in Davao City.' }}
                </p>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-gray-400">From</span>
                        <p class="font-display font-bold text-amber-400 text-xl">₱{{ number_format($tour->price, 2) }}</p>
                        <span class="text-xs text-gray-400">per person</span>
                    </div>
                    <a href="{{ route('client.show', $tour->id) }}"
                       class="px-4 py-2.5 rounded-xl text-xs font-bold text-white transition-all hover:opacity-90 hover:shadow-md"
                       style="background:linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                        View Tour →
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-10">{{ $tours->withQueryString()->links() }}</div>
    @else
    <div class="text-center py-24">
        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#d6ece0;">
            <svg class="w-8 h-8" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <h3 class="font-display text-jungle-700 text-xl font-bold mb-2">No tours found</h3>
        <p class="text-gray-400 text-sm mb-6">Try a different search keyword.</p>
        <a href="{{ route('client.index') }}" class="text-amber-400 font-semibold text-sm hover:text-amber-500">View all tours →</a>
    </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
// ── Hero Carousel ──
let currentHeroSlide = 0;
const heroSlides = document.querySelectorAll('.hero-slide');

function nextHeroSlide() {
    if (heroSlides.length <= 1) return;
    heroSlides[currentHeroSlide].classList.remove('opacity-100');
    heroSlides[currentHeroSlide].classList.add('opacity-0');
    currentHeroSlide = (currentHeroSlide + 1) % heroSlides.length;
    heroSlides[currentHeroSlide].classList.remove('opacity-0');
    heroSlides[currentHeroSlide].classList.add('opacity-100');
}

if (heroSlides.length > 1) {
    setInterval(nextHeroSlide, 5000);
}
</script>
@endpush
