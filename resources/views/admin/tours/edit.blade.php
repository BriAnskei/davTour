@extends('layouts.admin')

@section('title', 'Edit Tour')
@section('page-title', 'Edit Tour')
@section('page-subtitle', 'Update tour details and images')

@section('content')

<div class="max-w-3xl">

    <form method="POST" action="{{ route('tours.update', $tour->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Info --}}
        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">
            <h2 class="font-display text-jungle-700 font-bold text-base mb-5">Tour Information</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Tour Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $tour->name) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 focus:ring-1 focus:ring-jungle-200 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Location <span class="text-red-400">*</span></label>
                    <input type="text" name="location" value="{{ old('location', $tour->location) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 focus:ring-1 focus:ring-jungle-200 @error('location') border-red-400 @enderror">
                    @error('location')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Price per Person (₱) <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">₱</span>
                        <input type="number" name="price" value="{{ old('price', $tour->price) }}" step="0.01" min="0"
                               class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 @error('price') border-red-400 @enderror">
                    </div>
                    @error('price')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Status <span class="text-red-400">*</span></label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="active" {{ old('status', $tour->status) === 'active' ? 'checked' : '' }} class="w-4 h-4">
                            <span class="text-sm text-gray-600 font-medium">Active</span>
                            <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="inactive" {{ old('status', $tour->status) === 'inactive' ? 'checked' : '' }} class="w-4 h-4">
                            <span class="text-sm text-gray-600 font-medium">Inactive</span>
                            <span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>
                        </label>
                    </div>
                    @error('status')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 resize-none @error('description') border-red-400 @enderror">{{ old('description', $tour->description) }}</textarea>
                    @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- Existing Images --}}
        @if($tour->images->isNotEmpty())
        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-jungle-700 font-bold text-base">Current Images</h2>
                <span class="text-xs text-gray-400">{{ $tour->images->count() }} photo(s)</span>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                @foreach($tour->images as $img)
                <div class="relative aspect-square rounded-xl overflow-hidden border border-slate2 group">
                    <img src="{{ asset('storage/' . $img->image) }}" class="w-full h-full object-cover transition-all group-[.marked-for-removal]:opacity-30">
                    
                    {{-- The Checkbox Logic --}}
                    <label class="absolute inset-0 cursor-pointer flex flex-col items-center justify-center p-2 text-center z-10">
                        <input type="checkbox" name="remove_images[]" value="{{ $img->id }}" 
                               class="peer hidden" 
                               onchange="this.closest('.relative').classList.toggle('marked-for-removal', this.checked)">
                        
                        {{-- Hover Overlay (Default) --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all peer-checked:bg-red-500/20"></div>

                        {{-- Icon --}}
                        <div class="relative w-8 h-8 rounded-full bg-white/20 border border-white/50 flex items-center justify-center text-white transition-all peer-checked:bg-red-500 peer-checked:border-red-500 hover:scale-110">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        
                        <span class="relative text-[10px] font-bold text-white mt-1 opacity-0 group-hover:opacity-100 transition-opacity uppercase tracking-wider peer-checked:opacity-100">
                             Remove
                        </span>
                    </label>

                    {{-- Red Border indicator when checked --}}
                    <div class="absolute inset-0 border-4 border-red-500 opacity-0 pointer-events-none transition-opacity peer-checked:opacity-100 rounded-xl z-20"></div>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-3">Click any image to <span class="text-red-500 font-bold">Mark for Removal</span>. They will be deleted when you Save Changes.</p>
        </div>
        @endif

        {{-- Upload New Images --}}
        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">
            <h2 class="font-display text-jungle-700 font-bold text-base mb-1">
                {{ $tour->images->isNotEmpty() ? 'Replace Images (Optional)' : 'Tour Images' }}
            </h2>
            <p class="text-gray-400 text-xs mb-5">JPG or PNG, max 2MB each.</p>

            <div class="border-2 border-dashed border-slate2 rounded-2xl p-10 text-center hover:border-jungle-500 transition-colors cursor-pointer"
                 onclick="document.getElementById('images-input').click()">
                <svg class="w-8 h-8 mx-auto mb-2" style="color:#2d6a4f;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-gray-500 font-medium">Click to upload</p>
                <input type="file" id="images-input" name="images[]" multiple accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="previewImages(this)">
            </div>
            <div id="image-previews" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-4 hidden"></div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all hover:shadow-lg"
                    style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                Save Changes
            </button>
            <a href="{{ route('tours.index') }}"
               class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-500 bg-white border border-slate2 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
        </div>

    </form>
</div>

@push('scripts')
<script>
function previewImages(input) {
    const container = document.getElementById('image-previews');
    container.innerHTML = '';
    if (input.files.length > 0) {
        container.classList.remove('hidden');
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-xl overflow-hidden border border-slate2';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endpush
@endsection