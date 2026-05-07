@extends('layouts.admin')

@section('title', 'Add New Tour')
@section('page-title', 'Add New Tour')
@section('page-subtitle', 'Create a new Davao City tour listing')

@section('content')

<div class="max-w-3xl">

    <form method="POST" action="{{ route('tours.store') }}" class="space-y-6">
        @csrf

        {{-- Basic Info --}}
        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">
            <h2 class="font-display text-jungle-700 font-bold text-base mb-5">Tour Information</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Name --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Tour Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="e.g. Mt. Apo Sunrise Trek"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 focus:ring-1 focus:ring-jungle-200 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Location --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Location <span class="text-red-400">*</span></label>
                    <input type="text" name="location" value="{{ old('location') }}"
                           placeholder="e.g. Davao City, Davao del Sur"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 focus:ring-1 focus:ring-jungle-200 @error('location') border-red-400 @enderror">
                    @error('location')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Price --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Price per Person (₱) <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">₱</span>
                        <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0"
                               placeholder="0.00"
                               class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 focus:ring-1 focus:ring-jungle-200 @error('price') border-red-400 @enderror">
                    </div>
                    @error('price')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Status --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Status <span class="text-red-400">*</span></label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="active" {{ old('status', 'active') === 'active' ? 'checked' : '' }}
                                   class="accent-jungle-700 w-4 h-4">
                            <span class="text-sm text-gray-600 font-medium">Active</span>
                            <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="inactive" {{ old('status') === 'inactive' ? 'checked' : '' }}
                                   class="accent-gray-400 w-4 h-4">
                            <span class="text-sm text-gray-600 font-medium">Inactive</span>
                            <span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>
                        </label>
                    </div>
                    @error('status')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">Description</label>
                    <textarea name="description" rows="4"
                              placeholder="Describe what makes this tour special — landmarks, activities, inclusions..."
                              class="w-full px-4 py-2.5 rounded-xl border border-slate2 text-sm focus:outline-none focus:border-jungle-500 focus:ring-1 focus:ring-jungle-200 resize-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- Images --}}
        <div class="bg-white rounded-2xl card-shine border border-slate2 p-6">

            <div class="flex items-center justify-between mb-1">
                <h2 class="font-display text-jungle-700 font-bold text-base">
                    Tour Images <span class="text-red-400">*</span>
                </h2>
                <span id="image-count-label" class="text-xs font-semibold text-gray-400">0 / 5</span>
            </div>
            <p class="text-gray-400 text-xs mb-3">Upload up to 5 images. JPG or PNG, max 2MB each.</p>

            {{-- Progress Bar --}}
            <div class="w-full h-1.5 bg-slate-100 rounded-full mb-5 overflow-hidden">
                <div id="image-progress-bar"
                     class="h-full rounded-full transition-all duration-300"
                     style="width: 0%; background: linear-gradient(90deg, #2d6a4f, #1a3a2a);"></div>
            </div>

            <div id="drop-zone"
                 class="border-2 border-dashed border-slate2 rounded-2xl p-10 text-center hover:border-jungle-500 transition-colors cursor-pointer"
                 onclick="document.getElementById('images-input').click()">
                <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:#d6ece0;">
                    <svg class="w-6 h-6" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-500 font-medium" id="drop-zone-label">Click to upload images</p>
                <p class="text-xs text-gray-400 mt-1">or drag & drop here</p>
                <input type="file" id="images-input" multiple accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="handleFiles(this.files)">
            </div>

            {{-- Base64 hidden inputs injected here by JS --}}
            <div id="base64-inputs"></div>

            {{-- Image Previews --}}
            <div id="image-previews" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-4 hidden"></div>

            @error('images_base64')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
            @error('images_base64.*')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 hover:shadow-lg"
                    style="background: linear-gradient(135deg,#1a3a2a,#2d6a4f);">
                Create Tour
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
    const MAX_IMAGES = 5;
    let imagePool = []; // { base64: string }

    function handleFiles(newFiles) {
        const remaining = MAX_IMAGES - imagePool.length;
        if (remaining <= 0) return;

        const accepted = Array.from(newFiles).slice(0, remaining);

        accepted.forEach(file => {
            // Reject non-image or oversized files silently
            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) return;
            if (file.size > 2 * 1024 * 1024) return;

            const reader = new FileReader();
            reader.onload = e => {
                imagePool.push({ base64: e.target.result });
                updateUI();
            };
            reader.readAsDataURL(file);
        });

        // Reset so same file can be re-added after removal
        document.getElementById('images-input').value = '';
    }

    function removeImage(index) {
        imagePool.splice(index, 1);
        updateUI();
    }

    function syncBase64Inputs() {
        const container = document.getElementById('base64-inputs');
        container.innerHTML = '';
        imagePool.forEach(item => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'images_base64[]';
            input.value = item.base64;
            container.appendChild(input);
        });
    }

    function updateUI() {
        const count = imagePool.length;
        const pct   = (count / MAX_IMAGES) * 100;

        syncBase64Inputs();

        // Progress bar
        document.getElementById('image-progress-bar').style.width = pct + '%';

        // Counter label
        document.getElementById('image-count-label').textContent = count + ' / ' + MAX_IMAGES;

        // Drop zone label
        const label = document.getElementById('drop-zone-label');
        if (count >= MAX_IMAGES) {
            label.textContent = 'Maximum of 5 images reached';
            label.classList.add('text-jungle-700');
            label.classList.remove('text-gray-500');
        } else {
            label.textContent = 'Click to upload images';
            label.classList.remove('text-jungle-700');
            label.classList.add('text-gray-500');
        }

        // Previews
        const container = document.getElementById('image-previews');
        container.innerHTML = '';

        if (count > 0) {
            container.classList.remove('hidden');
            imagePool.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-xl overflow-hidden border border-slate2 group';
                div.innerHTML = `
                    <img src="${item.base64}" class="w-full h-full object-cover">
                    <button
                        type="button"
                        onclick="removeImage(${index})"
                        class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/60 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500"
                        title="Remove image">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                `;
                container.appendChild(div);
            });
        } else {
            container.classList.add('hidden');
        }
    }

    // Drag & Drop
    const zone = document.getElementById('drop-zone');
    zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.classList.add('border-jungle-500', 'bg-jungle-50');
    });
    zone.addEventListener('dragleave', () => {
        zone.classList.remove('border-jungle-500', 'bg-jungle-50');
    });
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('border-jungle-500', 'bg-jungle-50');
        handleFiles(e.dataTransfer.files);
    });
</script>
@endpush
@endsection