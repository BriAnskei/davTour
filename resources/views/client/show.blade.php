<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tour->name }} — DavaoTours</title>
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
        .auth-input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45,106,79,.12);
        }

        /* Gallery */
        .thumb { transition: all .2s ease; }
        .thumb.active { border-color: #c9872a; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(10px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .fade-up { animation: fadeUp .4s ease forwards; }
    </style>
</head>
<body>

{{-- ===== NAVBAR ===== --}}
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
            @auth
                <span class="text-sm text-gray-500 hidden sm:block">Hi, <strong class="text-jungle-700">{{ Auth::user()->name }}</strong></span>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold text-white" style="background:#1a3a2a;">
                        Admin Panel →
                    </a>
                @else
                    <a href="{{ route('client.bookings') }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold" style="color:#1a3a2a; background:#d6ece0;">
                        My Bookings
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors">
                        Logout
                    </button>
                </form>
            @else
                <div class="relative" id="auth-wrapper">
                    <button id="auth-trigger" onclick="toggleAuthDropdown()"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90"
                            style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Sign In
                        <svg id="auth-chevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    {{-- Auth Dropdown --}}
                    <div id="auth-dropdown"
                         class="absolute right-0 top-full mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                        <div class="flex border-b border-gray-100 relative">
                            <button onclick="switchTab('login')" id="tab-login"
                                    class="flex-1 py-3.5 text-sm font-semibold transition-colors text-jungle-700">Sign In</button>
                            <button onclick="switchTab('register')" id="tab-register"
                                    class="flex-1 py-3.5 text-sm font-semibold transition-colors text-gray-400">Register</button>
                            <div id="tab-bar" class="absolute bottom-0 left-0 h-0.5 w-1/2 transition-transform duration-200" style="background:#1a3a2a;"></div>
                        </div>
                        <div id="auth-message" class="hidden mx-4 mt-3 px-3 py-2.5 rounded-xl text-xs font-medium"></div>
                        {{-- Login --}}
                        <div id="panel-login" class="p-5">
                            <form id="form-login" onsubmit="submitLogin(event)" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                                    <input type="email" name="email" id="login-email" placeholder="you@example.com"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all">
                                    <p id="err-login-email" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                                    <div class="relative">
                                        <input type="password" id="login-password" placeholder="••••••••"
                                               class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all pr-10">
                                        <button type="button" onclick="toggleVis('login-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" id="btn-login"
                                        class="w-full py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 mt-1"
                                        style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">Sign In</button>
                            </form>
                        </div>
                        {{-- Register --}}
                        <div id="panel-register" class="p-5 hidden">
                            <form id="form-register" onsubmit="submitRegister(event)" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Full Name</label>
                                    <input type="text" id="reg-name" placeholder="Juan dela Cruz"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                    <p id="err-reg-name" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                                    <input type="email" id="reg-email" placeholder="you@example.com"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                    <p id="err-reg-email" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Contact <span class="text-gray-400 font-normal">(optional)</span></label>
                                    <input type="text" id="reg-contact" placeholder="09XX XXX XXXX"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                                    <input type="password" id="reg-password" placeholder="Min. 6 characters"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                    <p id="err-reg-password" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Confirm Password</label>
                                    <input type="password" id="reg-confirm" placeholder="Re-enter password"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                </div>
                                <button type="submit" id="btn-register"
                                        class="w-full py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 mt-1"
                                        style="background: linear-gradient(135deg,#c9872a,#e8a83c);">Create Account</button>
                            </form>
                        </div>
                        <div class="px-5 pb-4 text-center">
                            <p class="text-xs text-gray-400">By signing in, you agree to our <span class="text-jungle-700 font-medium">Terms of Service</span></p>
                        </div>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</nav>

{{-- ===== MAIN CONTENT ===== --}}
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

            {{-- Image Gallery --}}
            <div class="bg-white rounded-2xl card-shine overflow-hidden">
                <div class="relative h-80 overflow-hidden bg-jungle-100">
                    @if($tour->images->isNotEmpty())
                        <img id="main-image"
                             src="{{ asset('storage/' . $tour->images->first()->image) }}"
                             alt="{{ $tour->name }}"
                             class="w-full h-full object-cover transition-opacity duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center"
                             style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                            <svg class="w-16 h-16 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($tour->images->count() > 1)
                <div class="p-4 flex gap-3 overflow-x-auto">
                    @foreach($tour->images as $index => $img)
                    <button onclick="switchImage('{{ asset('storage/' . $img->image) }}', this)"
                            class="thumb shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 {{ $index === 0 ? 'active border-amber-400' : 'border-transparent' }}">
                        <img src="{{ asset('storage/' . $img->image) }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- About --}}
            <div class="bg-white rounded-2xl card-shine p-6">
                <h2 class="font-display text-jungle-700 font-bold text-lg mb-3">About This Tour</h2>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ $tour->description ?? 'Experience the best of Davao City with this amazing tour.' }}
                </p>
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
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">
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

<footer class="mt-16 py-8 border-t border-gray-200 text-center text-xs text-gray-400">
    © {{ date('Y') }} DavaoTours — Proudly showcasing Davao City, Philippines 🇵🇭
</footer>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Image gallery
function switchImage(src, btn) {
    document.getElementById('main-image').src = src;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active', 'border-amber-400'));
    btn.classList.add('active', 'border-amber-400');
}

// Auth dropdown
function toggleAuthDropdown() {
    const dropdown = document.getElementById('auth-dropdown');
    const chevron  = document.getElementById('auth-chevron');
    if (!dropdown) return;
    dropdown.classList.toggle('open');
    if (chevron) chevron.style.transform = dropdown.classList.contains('open') ? 'rotate(180deg)' : '';
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('auth-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        const dropdown = document.getElementById('auth-dropdown');
        if (dropdown) dropdown.classList.remove('open');
        const chevron = document.getElementById('auth-chevron');
        if (chevron) chevron.style.transform = '';
    }
});

function switchTab(tab) {
    const isLogin = tab === 'login';
    document.getElementById('panel-login').classList.toggle('hidden', !isLogin);
    document.getElementById('panel-register').classList.toggle('hidden', isLogin);
    document.getElementById('tab-login').className    = `flex-1 py-3.5 text-sm font-semibold transition-colors ${isLogin ? 'text-jungle-700' : 'text-gray-400'}`;
    document.getElementById('tab-register').className = `flex-1 py-3.5 text-sm font-semibold transition-colors ${!isLogin ? 'text-jungle-700' : 'text-gray-400'}`;
    document.getElementById('tab-bar').style.transform = isLogin ? 'translateX(0)' : 'translateX(100%)';
    clearMessage();
}

function showMessage(msg, type = 'error') {
    const el = document.getElementById('auth-message');
    el.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'border-red-200', 'bg-jungle-50', 'text-jungle-700', 'border-jungle-100');
    el.classList.add(type === 'error' ? 'bg-red-50' : 'bg-jungle-50', type === 'error' ? 'text-red-600' : 'text-jungle-700', 'border', type === 'error' ? 'border-red-200' : 'border-jungle-100');
    el.textContent = msg;
}

function clearMessage() {
    const el = document.getElementById('auth-message');
    el.classList.add('hidden');
    el.textContent = '';
}

function showFieldError(id, msg) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg; el.classList.remove('hidden'); }
}

function toggleVis(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    btn.disabled = loading;
    btn.style.opacity = loading ? '0.7' : '1';
    btn.textContent = loading ? 'Please wait...' : (btnId === 'btn-login' ? 'Sign In' : 'Create Account');
}

async function submitLogin(e) {
    e.preventDefault();
    clearMessage();
    setLoading('btn-login', true);
    const data = new FormData();
    data.append('email',    document.getElementById('login-email').value);
    data.append('password', document.getElementById('login-password').value);
    data.append('_token',   CSRF);
    try {
        const res  = await fetch('{{ route("login.post") }}', { method: 'POST', body: data });
        const json = await res.json();
        if (json.success) {
            window.location.href = json.redirect;
        } else {
            showMessage(json.message || 'Login failed.');
        }
    } catch { showMessage('Something went wrong.'); }
    finally { setLoading('btn-login', false); }
}

async function submitRegister(e) {
    e.preventDefault();
    clearMessage();
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
            if (json.errors.name)     showFieldError('err-reg-name',     json.errors.name[0]);
            if (json.errors.email)    showFieldError('err-reg-email',    json.errors.email[0]);
            if (json.errors.password) showFieldError('err-reg-password', json.errors.password[0]);
        } else {
            showMessage(json.message || 'Registration failed.');
        }
    } catch { showMessage('Something went wrong.'); }
    finally { setLoading('btn-register', false); }
}
</script>

</body>
</html>