<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Explore Davao Tours')</title>
    
    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    {{-- Tailwind --}}
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

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .fade-up { animation: fadeUp .4s ease both; }
        
        /* Auth input focus ring */
        .auth-input:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45,106,79,.12);
        }
    </style>
    @stack('styles')
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
                {{-- Notification bell --}}
                <div class="relative mr-2" id="client-notification-dropdown">
                    <button onclick="toggleNotifications()" class="relative w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-jungle-50 hover:text-jungle-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span id="notif-badge" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div id="notif-menu" class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden hidden z-50">
                        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 class="text-[10px] font-bold text-jungle-700 uppercase tracking-widest">Notifications</h3>
                            <button onclick="markAllAsRead()" class="text-[10px] font-bold text-amber-500 hover:text-amber-600 uppercase tracking-widest">Clear All</button>
                        </div>
                        <div id="notif-list" class="max-h-96 overflow-y-auto">
                            <div class="p-8 text-center text-gray-400 text-xs italic">No new notifications</div>
                        </div>
                        <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
                            <a href="{{ route('client.bookings') }}" class="text-[10px] font-bold text-jungle-500 uppercase tracking-widest hover:text-jungle-700">View My Bookings</a>
                        </div>
                    </div>
                </div>

                {{-- User --}}
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

                <form method="POST" action="{{ route('logout') }}" class="inline"
                      onsubmit="confirmAction(event, {
                          title: 'Sign Out?',
                          description: 'Are you sure you want to log out of your account?',
                          confirmText: 'Log Out',
                          variant: 'danger'
                      })">
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
                        <svg id="auth-chevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
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
                            <div id="tab-bar" class="absolute bottom-0 left-0 h-0.5 w-1/2 transition-transform duration-200"
                                 style="background:#1a3a2a;"></div>
                        </div>

                        <div id="auth-message" class="hidden mx-4 mt-3 px-3 py-2.5 rounded-xl text-xs font-medium"></div>

                        {{-- LOGIN FORM --}}
                        <div id="panel-login" class="p-5">
                            <form id="form-login" onsubmit="submitLogin(event)" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                                    <input type="email" id="login-email" placeholder="you@example.com"
                                           class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all">
                                    <p id="err-login-email" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                                    <div class="relative">
                                        <input type="password" id="login-password" placeholder="••••••••"
                                               class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm transition-all pr-10">
                                        <button type="button" onclick="toggleVis('login-password')"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" id="btn-login"
                                        class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 mt-1"
                                        style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                                    Sign In
                                </button>
                                <div class="relative py-2 flex items-center">
                                    <div class="flex-grow border-t border-gray-100"></div>
                                    <span class="flex-shrink mx-3 text-[10px] font-bold text-gray-300 uppercase tracking-widest">OR</span>
                                    <div class="flex-grow border-t border-gray-100"></div>
                                </div>
                                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-2.5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                    Continue with Google
                                </a>
                            </form>
                        </div>

                        {{-- REGISTER FORM --}}
                        <div id="panel-register" class="p-5 hidden">
                            <form id="form-register" onsubmit="submitRegister(event)" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Full Name</label>
                                    <input type="text" id="reg-name" placeholder="Juan dela Cruz" class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                    <p id="err-reg-name" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                                    <input type="email" id="reg-email" placeholder="you@example.com" class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                    <p id="err-reg-email" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Contact <span class="text-gray-400 font-normal">(optional)</span></label>
                                    <input type="text" id="reg-contact" placeholder="09XX XXX XXXX" class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                                    <input type="password" id="reg-password" placeholder="Min. 6 characters" class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                    <p id="err-reg-password" class="text-red-400 text-xs mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Confirm Password</label>
                                    <input type="password" id="reg-confirm" placeholder="Re-enter password" class="auth-input w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm">
                                </div>
                                <button type="submit" id="btn-register" class="w-full py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 mt-1" style="background: linear-gradient(135deg,#c9872a,#e8a83c);">Create Account</button>
                                <div class="relative py-2 flex items-center">
                                    <div class="flex-grow border-t border-gray-100"></div>
                                    <span class="flex-shrink mx-3 text-[10px] font-bold text-gray-300 uppercase tracking-widest">OR</span>
                                    <div class="flex-grow border-t border-gray-100"></div>
                                </div>
                                <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-2.5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                    Sign up with Google
                                </a>
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

@yield('content')

{{-- ===== REUSABLE CONFIRMATION MODAL ===== --}}
<div id="confirm-modal" class="fixed inset-0 z-[60] flex items-center justify-center px-4 hidden">
    <div class="absolute inset-0 bg-jungle-700/40 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <div class="bg-white rounded-3xl max-w-sm w-full p-8 shadow-2xl relative animate-[fadeUp_0.3s_ease-out] border border-gray-100">
        <div id="confirm-icon-container" class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mb-6">
            <svg id="confirm-icon" class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        
        <h3 id="confirm-title" class="font-display font-bold text-jungle-700 text-2xl mb-2">Are you sure?</h3>
        <p id="confirm-description" class="text-gray-500 text-sm leading-relaxed mb-8">This action cannot be undone. Please confirm to proceed.</p>

        <div class="flex gap-3">
            <button onclick="closeConfirmModal()" 
                    class="flex-1 py-3 rounded-xl text-sm font-bold text-gray-400 bg-gray-50 hover:bg-gray-100 transition-all">
                Cancel
            </button>
            <button id="confirm-proceed-btn" 
                    class="flex-1 py-3 rounded-xl text-sm font-bold text-white transition-all shadow-md bg-red-500 hover:bg-red-600">
                Confirm
            </button>
        </div>
    </div>
</div>

<footer class="mt-16 py-8 border-t border-gray-200 text-center text-xs text-gray-400">
    © {{ date('Y') }} DavaoTours — Proudly showcasing Davao City, Philippines 🇵🇭
</footer>

{{-- Scripts --}}
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Reusable Confirmation Modal
let confirmCallback = null;

function openConfirmModal(options = {}) {
    const modal = document.getElementById('confirm-modal');
    const title = document.getElementById('confirm-title');
    const desc  = document.getElementById('confirm-description');
    const icon  = document.getElementById('confirm-icon');
    const iconCont = document.getElementById('confirm-icon-container');
    const proceedBtn = document.getElementById('confirm-proceed-btn');

    title.textContent = options.title || 'Are you sure?';
    desc.textContent  = options.description || 'This action cannot be undone.';
    proceedBtn.textContent = options.confirmText || 'Confirm';
    
    // Theme colors
    if (options.variant === 'danger') {
        iconCont.className = 'w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mb-6';
        icon.className = 'w-8 h-8 text-red-500';
        proceedBtn.className = 'flex-1 py-3 rounded-xl text-sm font-bold text-white transition-all shadow-md bg-red-500 hover:bg-red-600';
    } else {
        iconCont.className = 'w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mb-6';
        icon.className = 'w-8 h-8 text-amber-500';
        proceedBtn.className = 'flex-1 py-3 rounded-xl text-sm font-bold text-white transition-all shadow-md bg-amber-500 hover:bg-amber-600';
    }

    confirmCallback = options.onConfirm || null;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    confirmCallback = null;
}

document.getElementById('confirm-proceed-btn').addEventListener('click', () => {
    if (confirmCallback) confirmCallback();
    closeConfirmModal();
});

function confirmAction(e, options = {}) {
    e.preventDefault();
    const form = e.target.closest('form');
    openConfirmModal({
        ...options,
        onConfirm: () => {
            if (form) form.submit();
        }
    });
}

// Auth Dropdown
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
        const d = document.getElementById('auth-dropdown');
        if (d) d.classList.remove('open');
        const c = document.getElementById('auth-chevron');
        if (c) c.style.transform = '';
    }

    const notifWrapper = document.getElementById('client-notification-dropdown');
    if (notifWrapper && !notifWrapper.contains(e.target)) {
        document.getElementById('notif-menu').classList.add('hidden');
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
    if (!el) return;
    el.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'border-red-200', 'bg-jungle-50', 'text-jungle-700', 'border-jungle-100');
    el.classList.add(type === 'error' ? 'bg-red-50' : 'bg-jungle-50', type === 'error' ? 'text-red-600' : 'text-jungle-700', 'border', type === 'error' ? 'border-red-200' : 'border-jungle-100');
    el.textContent = msg;
}

function clearMessage() {
    const el = document.getElementById('auth-message');
    if (el) el.classList.add('hidden');
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
    if (!btn) return;
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
        if (json.success) window.location.href = json.redirect;
        else showMessage(json.message || 'Login failed.');
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
        if (json.success) window.location.href = json.redirect;
        else if (json.errors) {
            if (json.errors.name)     showFieldError('err-reg-name',     json.errors.name[0]);
            if (json.errors.email)    showFieldError('err-reg-email',    json.errors.email[0]);
            if (json.errors.password) showFieldError('err-reg-password', json.errors.password[0]);
        } else showMessage(json.message || 'Registration failed.');
    } catch { showMessage('Something went wrong.'); }
    finally { setLoading('btn-register', false); }
}

// Notifications
function toggleNotifications() {
    const menu = document.getElementById('notif-menu');
    menu.classList.toggle('hidden');
    if (!menu.classList.contains('hidden')) fetchNotifications();
}

function fetchNotifications() {
    fetch('{{ route("client.notifications.index") }}')
        .then(res => res.json())
        .then(notifs => {
            const list = document.getElementById('notif-list');
            const badge = document.getElementById('notif-badge');
            
            if (notifs.length > 0) {
                badge.textContent = notifs.length;
                badge.classList.remove('hidden');
                list.innerHTML = '';
                notifs.forEach(n => {
                    const item = document.createElement('a');
                    item.href = '{{ url("/client/bookings") }}/' + n.data.booking_id;
                    item.className = 'block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition-colors';
                    item.innerHTML = `
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-lg ${getNotifColor(n.data.type)} flex items-center justify-center shrink-0">
                                ${getNotifIcon(n.data.type)}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold text-jungle-700 leading-tight mb-0.5">${n.data.message}</p>
                                <p class="text-[10px] text-gray-400">${n.data.tour_name}</p>
                            </div>
                        </div>
                    `;
                    list.appendChild(item);
                });
            } else {
                badge.classList.add('hidden');
                list.innerHTML = '<div class="p-8 text-center text-gray-400 text-xs italic">No new notifications</div>';
            }
        });
}

function getNotifColor(type) {
    if (type === 'senior_approved' || type === 'booking_confirmed') return 'bg-jungle-50 text-jungle-500';
    if (type === 'senior_rejected') return 'bg-red-50 text-red-500';
    return 'bg-amber-50 text-amber-500';
}

function getNotifIcon(type) {
    if (type === 'senior_approved' || type === 'booking_confirmed') return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
    if (type === 'senior_rejected') return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
    return '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
}

function markAllAsRead() {
    fetch('{{ route("client.notifications.markAsRead") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
    }).then(() => {
        document.getElementById('notif-badge').classList.add('hidden');
        document.getElementById('notif-list').innerHTML = '<div class="p-8 text-center text-gray-400 text-xs italic">No new notifications</div>';
    });
}

@auth
    setInterval(fetchNotifications, 60000);
    fetchNotifications();
@endauth
</script>
@stack('scripts')
</body>
</html>