@extends('layouts.admin')

@section('title', 'Senior ID Validation')
@section('page-title', 'Senior Citizen ID Validation')
@section('page-subtitle', 'Verify images and approve or reject the senior discount')

@section('content')
<div class="max-w-4xl">
    {{-- Back Link --}}
    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-jungle-500 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Bookings
    </a>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Booking Info --}}
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl card-shine p-6 border border-slate2">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Booking Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Guest</p>
                        <p class="text-sm font-semibold text-jungle-700">{{ $booking->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $booking->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Tour</p>
                        <p class="text-sm font-semibold text-jungle-700">{{ $booking->tourSchedule->tour->name }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->tourSchedule->date)->format('M d, Y') }}</p>
                    </div>
                    <div class="flex justify-between items-end border-t border-slate2 pt-4">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Pax Breakdown</p>
                            <p class="text-sm text-gray-600">Total: <span class="font-bold">{{ $booking->p_count }}</span></p>
                            <p class="text-sm text-amber-500 font-bold">Seniors: {{ $booking->senior_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Form --}}
            <div class="bg-white rounded-2xl card-shine p-6 border border-slate2">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Validation Decision</h3>
                
                <form action="{{ route('admin.bookings.validate.post', $booking->id) }}" method="POST" class="space-y-4">
                    @csrf

                    @if($errors->any())
                        <div class="p-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-xs">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Decision</label>
                        
                        {{-- Hidden Radios as Peers --}}
                        <input type="radio" name="action" value="approve" id="approve-radio" class="peer/approve sr-only" {{ old('action', $booking->status) !== 'rejected' ? 'checked' : '' }}>
                        <input type="radio" name="action" value="reject" id="reject-radio" class="peer/reject sr-only" {{ old('action', $booking->status) === 'rejected' ? 'checked' : '' }}>

                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <label for="approve-radio" class="block px-4 py-3 rounded-xl border border-slate2 text-center text-xs font-bold text-gray-500 cursor-pointer peer-checked/approve:border-jungle-500 peer-checked/approve:bg-jungle-50 peer-checked/approve:text-jungle-700 transition-all">
                                Approve
                            </label>
                            <label for="reject-radio" class="block px-4 py-3 rounded-xl border border-slate2 text-center text-xs font-bold text-gray-500 cursor-pointer peer-checked/reject:border-red-500 peer-checked/reject:bg-red-50 peer-checked:text-red-700 transition-all">
                                Reject
                            </label>
                        </div>

                        {{-- Reason Container: Shown only when Reject is checked --}}
                        <div class="hidden peer-checked/reject:block space-y-4 pt-2 border-t border-slate2 mt-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-2">Reason for Rejection</label>
                                <select id="predefined-reason" class="w-full px-4 py-3 rounded-xl border border-slate2 text-xs mb-2 focus:outline-none focus:border-red-500" onchange="updateReasonText(this.value)">
                                    <option value="">-- Select a reason --</option>
                                    <option value="ID photo is blurry or unreadable.">Blurry Image</option>
                                    <option value="Invalid ID type (not a Senior Citizen ID).">Invalid ID Type</option>
                                    <option value="Name on ID does not match account name.">Name Mismatch</option>
                                    <option value="Senior Citizen count mismatch.">Count Mismatch</option>
                                    <option value="Other">Other (Type below)</option>
                                </select>
                                <textarea id="reason-text" name="reason" rows="3" placeholder="Additional details..."
                                          class="w-full px-4 py-3 rounded-xl border border-slate2 text-xs focus:outline-none focus:border-red-500">{{ old('reason', $booking->rejection_reason) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-jungle-700 text-white text-xs font-bold hover:bg-jungle-800 transition-all shadow-md">
                        Submit Decision
                    </button>
                </form>
            </div>
        </div>

        {{-- ID Images --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl card-shine p-6 border border-slate2 min-h-[400px]">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Uploaded ID Images ({{ $booking->seniorImages->count() }})</h3>
                
                @if($booking->seniorImages->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                        <svg class="w-12 h-12 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="1.5"/></svg>
                        <p class="text-sm font-medium">No images uploaded for this booking.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($booking->seniorImages as $image)
                            <div class="group relative rounded-2xl overflow-hidden border border-slate2 bg-cream-100 aspect-[4/3]">
                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                     class="w-full h-full object-contain cursor-zoom-in"
                                     onclick="openLightbox('{{ asset('storage/' . $image->image_path) }}')">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                    <span class="text-white text-xs font-bold px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md">Click to Enlarge</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 hidden" onclick="closeLightbox()">
    <button class="absolute top-6 right-6 text-white hover:text-amber-400 transition-colors">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="lightbox-img" class="max-w-full max-h-full rounded-lg shadow-2xl animate-[zoomIn_0.3s_ease-out]">
</div>

@endsection

@push('scripts')
<script>
    function updateReasonText(value) {
        const textarea = document.getElementById('reason-text');
        if (value === 'Other') {
            textarea.value = '';
            textarea.focus();
        } else if (value !== '') {
            textarea.value = value;
        }
    }

    function openLightbox(src) {
        const lb = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        img.src = src;
        lb.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        const lb = document.getElementById('lightbox');
        lb.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    @keyframes zoomIn {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</script>
@endpush
