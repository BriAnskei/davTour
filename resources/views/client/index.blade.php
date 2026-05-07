<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Explore Davao Tours</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        jungle: { DEFAULT: '#1a3a2a', 50: '#f0f7f3', 100: '#d6ece0', 500: '#2d6a4f', 700: '#1a3a2a' },
                        amber:  { DEFAULT: '#c9872a', 100: '#f9e3bb', 300: '#e8a83c', 400: '#c9872a' },
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

        /* Auth dropdown */
        #auth-dropdown {
            transform: translateY(-8px);
            opacity: 0;
            pointer-events: none;
            transition: all .22s cubic-bezier(.4,0,.2,1);
        }
        #auth-dropdown.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        /* Tab indicator */
        .tab-indicator {
            transition: transform .2s ease, width .2s ease;
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .tour-card { animation: fadeUp .4s ease both; }
        .tour-card:nth-child(2) { animation-delay:.07s; }
        .tour-card:nth-child(3) { animation-delay:.14s; }
        .tour-card:nth-child(4) { animation-delay:.21s; }
        .tour-card:nth-child(5) { animation-delay:.28s; }
        .tour-card:nth-child(6) { animation-delay:.35s; }

        /* Input focus ring */
        .auth-input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45,106,79,.12);
        }
    </style>
</head>
<body>

{{-- ===== NAVBAR ===== --}}
<nav class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-100"
     style="box-shadow:0 1px 8px rgba(26,58,42,.07);">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('client.index') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#c9872a;">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                </svg>
            </div>
            <span class="font-display font-bold text-jungle-700 text-lg">DavaoTours</span>
        </a>

        {{-- Right side --}}
        <div class="flex items-center gap-3">
            @auth
                {{-- Logged in state --}}
                <span class="text-sm text-gray-500 hidden sm:block">Hi, <strong class="text-jungle-700">{{ Auth::user()->name }}</strong></span>

                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:opacity-90"
                       style="background:#1a3a2a;">
                        Admin Panel →
                    </a>
                @else
                    <a href="{{ route('client.bookings') }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold transition-all"
                       style="color:#1a3a2a; background:#d6ece0;">
                        My Bookings
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors">
                        Logout
                    </button>
                </form>

            @else
                {{-- Auth dropdown trigger --}}
                <div class="relative" id="auth-wrapper">
                    <button id="auth-trigger"
                            onclick="toggleAuthDropdown()"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 hover:shadow-md"
                            style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Sign In
                       
                    </button>

                    {{-- ===== AUTH DROPDOWN ===== --}}
                    <div id="auth-dropdown"
                         class="absolute right-0 top-full mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">

                        {{-- Tabs --}}
                        <div class="flex border-b border-gray-100 relative">
                            <button onclick="switchTab('login')" id="tab-login"
                                    class="flex-1 py-3.5 text-sm font-semibold transition-colors text-jungle-700">
                                Sign In
                            </button>
                            <button onclick="switchTab('register')" id="tab-register"
                                    class="flex-1 py-3.5 text-sm font-semibold transition-colors text-gray-400">
                                Register
                            </button>
                            {{-- Sliding underline --}}
                            <div id="tab-bar" class="absolute bottom-0 left-0 h-0.5 w-1/2 transition-transform duration-200"
                                 style="background:#1a3a2a;"></div>
                        </div>

                        {{-- Global error/success message --}}
                        <div id="auth-message" class="hidden mx-4 mt-3 px-3 py-2.5 rounded-xl text-xs font-medium"></div>

                        {{-- ===== LOGIN FORM ===== --}}
                        <div id="panel-login" class="p-5">
                            <form id="form-login" onsubmit="submitLogin(event)" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                                    <input type="email" name="email" id="login-email"
                                           placeholder="you@example.com"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all">
                                    <p id="err-login-email" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                                    <div class="relative">
                                        <input type="password" name="password" id="login-password"
                                               placeholder="••••••••"
                                               class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all pr-10">
                                        <button type="button" onclick="toggleVis('login-password')"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                    <p id="err-login-password" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <button type="submit" id="btn-login"
                                        class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 hover:shadow-md mt-1"
                                        style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                                    Sign In
                                </button>

                                <div class="relative py-2 flex items-center">
                                    <div class="flex-grow border-t border-gray-100"></div>
                                    <span class="flex-shrink mx-3 text-[10px] font-bold text-gray-300 uppercase tracking-widest">OR</span>
                                    <div class="flex-grow border-t border-gray-100"></div>
                                </div>

                                <a href="{{ route('google.login') }}" 
                                   class="w-full flex items-center justify-center gap-2.5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                    Continue with Google
                                </a>
                            </form>
                        </div>

                        {{-- ===== REGISTER FORM ===== --}}
                        <div id="panel-register" class="p-5 hidden">
                            <form id="form-register" onsubmit="submitRegister(event)" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Full Name</label>
                                    <input type="text" name="name" id="reg-name"
                                           placeholder="Juan dela Cruz"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all">
                                    <p id="err-reg-name" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                                    <input type="email" name="email" id="reg-email"
                                           placeholder="you@example.com"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all">
                                    <p id="err-reg-email" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">
                                        Contact <span class="text-gray-400 font-normal">(optional)</span>
                                    </label>
                                    <input type="text" name="contact_number" id="reg-contact"
                                           placeholder="09XX XXX XXXX"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                                    <div class="relative">
                                        <input type="password" name="password" id="reg-password"
                                               placeholder="Min. 6 characters"
                                               class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all pr-10">
                                        <button type="button" onclick="toggleVis('reg-password')"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                    <p id="err-reg-password" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="reg-confirm"
                                           placeholder="Re-enter password"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all">
                                    <p id="err-reg-confirm" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <button type="submit" id="btn-register"
                                        class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 hover:shadow-md mt-1"
                                        style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
                                    Create Account
                                </button>

                                <div class="relative py-2 flex items-center">
                                    <div class="flex-grow border-t border-gray-100"></div>
                                    <span class="flex-shrink mx-3 text-[10px] font-bold text-gray-300 uppercase tracking-widest">OR</span>
                                    <div class="flex-grow border-t border-gray-100"></div>
                                </div>

                                <a href="{{ route('google.login') }}" 
                                   class="w-full flex items-center justify-center gap-2.5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                    Sign up with Google
                                </a>
                            </form>
                        </div>

                        {{-- Footer note --}}
                        <div class="px-5 pb-4 text-center">
                            <p class="text-xs text-gray-400">
                                By signing in, you agree to our
                                <span class="text-jungle-700 font-medium">Terms of Service</span>
                            </p>
                        </div>
                    </div>
                    {{-- End auth dropdown --}}
                </div>
            @endauth
        </div>
    </div>
</nav>

{{-- ===== HERO ===== --}}
<section class="relative overflow-hidden py-20 px-6"
         style="background: linear-gradient(135deg,#122a1e 0%,#1a3a2a 60%,#2d6a4f 100%);">
    <div class="absolute inset-0 opacity-10"
         style="background-image:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23c9872a\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative max-w-3xl mx-auto text-center">
        <p class="text-amber-300 text-xs font-semibold uppercase tracking-widest mb-3">Davao City, Philippines</p>
        <h1 class="font-display text-white text-4xl sm:text-5xl font-bold leading-tight mb-4">
            Discover the Pearl<br>of the South
        </h1>
        <p class="text-white/60 text-base mb-8 max-w-xl mx-auto">
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
                    class="px-5 py-3.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-all"
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

<footer class="mt-16 py-8 border-t border-gray-200 text-center text-xs text-gray-400">
    © {{ date('Y') }} DavaoTours — Proudly showcasing Davao City, Philippines 🇵🇭
</footer>

{{-- ===== SCRIPTS ===== --}}
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Dropdown toggle ──
function toggleAuthDropdown() {
    const dropdown = document.getElementById('auth-dropdown');
    const chevron  = document.getElementById('auth-chevron');
    dropdown.classList.toggle('open');
    chevron.style.transform = dropdown.classList.contains('open') ? 'rotate(180deg)' : '';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('auth-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('auth-dropdown').classList.remove('open');
        document.getElementById('auth-chevron').style.transform = '';
    }
});

// ── Tab switching ──
function switchTab(tab) {
    const isLogin = tab === 'login';

    document.getElementById('panel-login').classList.toggle('hidden', !isLogin);
    document.getElementById('panel-register').classList.toggle('hidden', isLogin);

    document.getElementById('tab-login').className    = `flex-1 py-3.5 text-sm font-semibold transition-colors ${isLogin ? 'text-jungle-700' : 'text-gray-400'}`;
    document.getElementById('tab-register').className = `flex-1 py-3.5 text-sm font-semibold transition-colors ${!isLogin ? 'text-jungle-700' : 'text-gray-400'}`;

    document.getElementById('tab-bar').style.transform = isLogin ? 'translateX(0)' : 'translateX(100%)';

    clearMessage();
    clearErrors();
}

// ── Show message ──
function showMessage(msg, type = 'error') {
    const el = document.getElementById('auth-message');
    el.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'border-red-200', 'bg-jungle-50', 'text-jungle-700', 'border-jungle-100');
    if (type === 'error') {
        el.classList.add('bg-red-50', 'text-red-600', 'border', 'border-red-200');
    } else {
        el.classList.add('bg-jungle-50', 'text-jungle-700', 'border', 'border-jungle-100');
    }
    el.textContent = msg;
}

function clearMessage() {
    const el = document.getElementById('auth-message');
    el.classList.add('hidden');
    el.textContent = '';
}

// ── Clear inline errors ──
function clearErrors() {
    document.querySelectorAll('[id^="err-"]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    document.querySelectorAll('.auth-input').forEach(el => {
        el.style.borderColor = '';
    });
}

function showFieldError(fieldId, msg) {
    const err = document.getElementById(fieldId);
    if (err) {
        err.textContent = msg;
        err.classList.remove('hidden');
    }
}

// ── Toggle password visibility ──
function toggleVis(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// ── Set button loading state ──
function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    btn.disabled = loading;
    btn.style.opacity = loading ? '0.7' : '1';
    btn.textContent = loading ? 'Please wait...' : (btnId === 'btn-login' ? 'Sign In' : 'Create Account');
}

// ── LOGIN ──
async function submitLogin(e) {
    e.preventDefault();
    clearMessage();
    clearErrors();
    setLoading('btn-login', true);

    const data = new FormData();
    data.append('email',    document.getElementById('login-email').value);
    data.append('password', document.getElementById('login-password').value);
    data.append('_token',   CSRF);

    try {
        const res  = await fetch('{{ route("login.post") }}', { method: 'POST', body: data });
        const json = await res.json();

        if (json.success) {
            // Admin → redirect to admin dashboard
            // Client → reload current page (stays on /client, now logged in)
            window.location.href = json.redirect;
        } else {
            showMessage(json.message || 'Login failed. Please try again.');
        }
    } catch (err) {
        showMessage('Something went wrong. Please try again.');
    } finally {
        setLoading('btn-login', false);
    }
}

// ── REGISTER ──
async function submitRegister(e) {
    e.preventDefault();
    clearMessage();
    clearErrors();
    setLoading('btn-register', true);

    const data = new FormData();
    data.append('name',                  document.getElementById('reg-name').value);
    data.append('email',                 document.getElementById('reg-email').value);
    data.append('contact_number',        document.getElementById('reg-contact').value);
    data.append('password',              document.getElementById('reg-password').value);
    data.append('password_confirmation', document.getElementById('reg-confirm').value);
    data.append('_token',                CSRF);

    try {
        const res  = await fetch('{{ route("register.post") }}', { method: 'POST', body: data });
        const json = await res.json();

        if (json.success) {
            window.location.href = json.redirect;
        } else if (json.errors) {
            // Show field-level errors
            if (json.errors.name)     showFieldError('err-reg-name',     json.errors.name[0]);
            if (json.errors.email)    showFieldError('err-reg-email',    json.errors.email[0]);
            if (json.errors.password) showFieldError('err-reg-password', json.errors.password[0]);
            if (json.errors.password_confirmation) showFieldError('err-reg-confirm', json.errors.password_confirmation[0]);
        } else {
            showMessage(json.message || 'Registration failed.');
        }
    } catch (err) {
        showMessage('Something went wrong. Please try again.');
    } finally {
        setLoading('btn-register', false);
    }
}
</script>

</body>
</html>