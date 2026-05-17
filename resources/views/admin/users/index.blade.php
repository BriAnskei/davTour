@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users Management')
@section('page-subtitle', 'View and manage all registered users')

@section('content')

{{-- Stats Row --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl card-shine border border-slate2 p-4">
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Total Users</p>
        <p class="font-display text-2xl font-bold text-jungle-700">{{ $totalUsers ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-2xl card-shine border border-slate2 p-4">
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Clients</p>
        <p class="font-display text-2xl font-bold text-amber-400">{{ $clientCount ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-2xl card-shine border border-slate2 p-4">
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Admins</p>
        <p class="font-display text-2xl font-bold" style="color:#8b5e3c;">{{ $adminCount ?? 0 }}</p>
    </div>
</div>

{{-- Filter + Search --}}
<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <div class="flex gap-2">
        @foreach(['all' => 'All', 'user' => 'Clients', 'admin' => 'Admins'] as $val => $label)
        <a href="{{ route('admin.users.index', ['role' => $val === 'all' ? null : $val]) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors
               {{ (request('role', 'all') === $val || (!request('role') && $val === 'all')) ? 'text-white' : 'bg-white border border-slate2 text-gray-500 hover:bg-gray-50' }}"
           style="{{ (request('role', 'all') === $val || (!request('role') && $val === 'all')) ? 'background:#1a3a2a;' : '' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
        <input type="hidden" name="role" value="{{ request('role') }}">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                   class="pl-9 pr-4 py-2 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 bg-white w-56">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-400 text-white text-sm font-semibold hover:bg-amber-500 transition-colors">Search</button>
    </form>
</div>

{{-- Users Table --}}
<div class="bg-white rounded-2xl card-shine border border-slate2 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f5f1eb;" class="border-b border-slate2">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">User</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Contact</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Bookings</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Joined</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate2">
                @forelse($users as $user)
                <tr class="hover:bg-cream transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                 style="background: {{ $user->role === 'admin' ? '#c9872a' : '#2d6a4f' }};">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-jungle-700 text-sm">{{ $user->name }}</p>
                                <p class="text-gray-400 text-xs">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-xs">{{ $user->contact_number ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold
                            {{ $user->role === 'admin' ? 'bg-amber-100 text-amber-600' : 'bg-jungle-100 text-jungle-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-600">{{ $user->bookings_count ?? 0 }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Prevent deleting yourself --}}
                            @if($user->id !== Auth::id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                  onsubmit="return confirm('Delete user {{ $user->name }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-gray-300 italic px-2">You</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#d6ece0;">
                            <svg class="w-6 h-6" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No users found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-slate2">
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection