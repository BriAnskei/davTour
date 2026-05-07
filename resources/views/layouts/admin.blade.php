<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — DavaoTours</title>

    {{-- Google Fonts: Playfair Display + DM Sans --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CDN (replace with compiled asset if using Vite) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        jungle:  { DEFAULT: '#1a3a2a', 50: '#f0f7f3', 100: '#d6ece0', 200: '#a8d4bc', 500: '#2d6a4f', 600: '#1e5c42', 700: '#1a3a2a', 800: '#122a1e', 900: '#0a1a12' },
                        amber:   { DEFAULT: '#c9872a', 50: '#fdf6ec', 100: '#f9e3bb', 200: '#f2c878', 300: '#e8a83c', 400: '#c9872a', 500: '#a86d1e', 600: '#875515' },
                        earth:   { DEFAULT: '#8b5e3c', 100: '#f5ede6', 200: '#dbb99a', 400: '#a67c5b', 500: '#8b5e3c' },
                        cream:   { DEFAULT: '#faf6f0', 100: '#fdf9f5' },
                        slate2:  { DEFAULT: '#e8e2d9' },
                    },
                    fontFamily: {
                        display: ['Playfair Display', 'Georgia', 'serif'],
                        body:    ['DM Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f5f1eb; }
        .sidebar-link { transition: all .2s ease; }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(201,135,42,.15);
            color: #c9872a;
            border-left: 3px solid #c9872a;
        }
        .sidebar-link.active .nav-icon { color: #c9872a; }
        .card-shine { box-shadow: 0 1px 3px rgba(26,58,42,.08), 0 4px 16px rgba(26,58,42,.06); }
        @keyframes fadeSlide { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        .page-enter { animation: fadeSlide .35s ease forwards; }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #1a3a2a; }
        ::-webkit-scrollbar-thumb { background: #c9872a; border-radius: 4px; }
    </style>

    @stack('styles')
</head>
<body class="bg-cream-100">

{{-- ===== SIDEBAR ===== --}}
<aside id="sidebar"
       class="fixed top-0 left-0 h-screen w-64 bg-jungle-700 flex flex-col z-40 transition-transform duration-300"
       style="background: linear-gradient(180deg, #122a1e 0%, #1a3a2a 60%, #0f2318 100%);">

    {{-- Logo --}}
    <div class="px-6 py-6 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#c9872a;">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                </svg>
            </div>
            <div>
                <p class="font-display text-white font-bold text-base leading-tight">DavaoTours</p>
                <p class="text-white/40 text-xs">Admin Panel</p>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">

        <p class="text-white/30 text-xs font-semibold uppercase tracking-widest px-3 mb-2">Main</p>

        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 text-sm {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="nav-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <p class="text-white/30 text-xs font-semibold uppercase tracking-widest px-3 mt-4 mb-2">Tours</p>

        <a href="{{ route('tours.index') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 text-sm {{ request()->routeIs('tours.*') ? 'active' : '' }}">
            <svg class="nav-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            Tours
        </a>

        <a href="{{ route('tour_schedules.index') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 text-sm {{ request()->routeIs('tour_schedules.*') ? 'active' : '' }}">
            <svg class="nav-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Schedules
        </a>

        <p class="text-white/30 text-xs font-semibold uppercase tracking-widest px-3 mt-4 mb-2">Operations</p>

        <a href="{{ route('admin.bookings') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 text-sm {{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
            <svg class="nav-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Bookings
        </a>

        <a href="{{ route('payments.index') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 text-sm {{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <svg class="nav-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Payments / POS
        </a>

        <p class="text-white/30 text-xs font-semibold uppercase tracking-widest px-3 mt-4 mb-2">System</p>

        <a href="{{ route('admin.users') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 text-sm {{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <svg class="nav-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Users
        </a>

    </nav>

    {{-- Admin profile --}}
    <div class="px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center text-white font-bold text-xs">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-semibold truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                <p class="text-white/40 text-xs truncate">Administrator</p>
            </div>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="text-white/40 hover:text-amber-400 transition-colors" title="Logout">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </div>
</aside>

{{-- ===== MAIN AREA ===== --}}
<div class="ml-64 min-h-screen flex flex-col">

    {{-- Top Navbar --}}
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate2 px-6 py-3 flex items-center justify-between" style="box-shadow: 0 1px 8px rgba(26,58,42,.07);">
        <div>
            <h1 class="font-display text-jungle-700 text-lg font-bold leading-tight">@yield('page-title', 'Dashboard')</h1>
            <p class="text-xs text-gray-400 font-body">@yield('page-subtitle', 'Welcome back, Administrator')</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Date --}}
            <div class="text-xs text-gray-400 hidden md:block">
                {{ now()->format('l, F j Y') }}
            </div>
            {{-- Notification bell placeholder --}}
            <button class="relative w-8 h-8 rounded-lg bg-jungle-50 flex items-center justify-center text-jungle-500 hover:bg-amber-50 hover:text-amber-400 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-jungle-50 border border-jungle-200 text-jungle-700 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-jungle-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Page Content --}}
    <main class="flex-1 p-6 page-enter">
        @yield('content')
    </main>

    <footer class="px-6 py-3 text-xs text-gray-400 border-t border-slate2 text-center">
        © {{ date('Y') }} DavaoTours Admin Panel — Proudly built for Davao City
    </footer>
</div>

@stack('scripts')
</body>
</html>