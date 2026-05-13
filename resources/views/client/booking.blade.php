<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tour — DavaoTours</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        jungle: { DEFAULT: '#1a3a2a', 50: '#f0f7f3', 100: '#d6ece0', 500: '#2d6a4f', 700: '#1a3a2a' },
                        amber:  { DEFAULT: '#c9872a', 100: '#f9e3bb', 400: '#c9872a' },
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
        @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp .4s ease forwards; }
    </style>
</head>
<body>

{{-- Navbar --}}
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
            <span class="text-sm text-gray-500 hidden sm:block">
                Hi, <strong class="text-jungle-700">{{ Auth::user()->name }}</strong>
            </span>
            <a href="{{ route('client.bookings') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold"
               style="color:#1a3a2a; background:#d6ece0;">My Bookings</a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="max-w-3xl mx-auto px-6 py-10">

    {{-- Back --}}
    <a href="{{ route('client.show', $schedule->tour->id) }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-jungle-700 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Tour
    </a>

    {{-- Error / Cancellation message --}}
    @if(session('error'))
    <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    @if(request('cancelled'))
    <div class="mb-5 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Payment was cancelled. You can try again below.
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-5 gap-6">

        {{-- Booking Form --}}
        <div class="sm:col-span-3 fade-up">
            <div class="bg-white rounded-2xl card-shine p-6">
                <h1 class="font-display text-jungle-700 text-xl font-bold mb-1">Complete Your Booking</h1>
                <p class="text-gray-400 text-sm mb-6">Review your details and proceed to payment.</p>

                @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                {{-- Posts to Stripe checkout --}}
                <form method="POST" action="{{ route('client.payment.checkout') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <input type="hidden" name="tour_sched_id" value="{{ $schedule->id }}">
                    @if($existingBooking)
                        <input type="hidden" name="booking_id" value="{{ $existingBooking->id }}">
                    @endif

                    {{-- Guest name (read-only) --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">Full Name</label>
                        <input type="text" value="{{ Auth::user()->name }}" disabled
                               class="w-full px-4 py-3 rounded-xl border border-gray-100 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>

                    {{-- Email (read-only) --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">Email</label>
                        <input type="email" value="{{ Auth::user()->email }}" disabled
                               class="w-full px-4 py-3 rounded-xl border border-gray-100 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>

                    {{-- Number of persons --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">
                            Number of Persons <span class="text-red-400">*</span>
                        </label>
                        <input type="number" id="p_count" name="p_count" value="{{ old('p_count', $existingBooking ? $existingBooking->p_count : 1) }}"
                               min="1" max="{{ $remaining }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                                      focus:outline-none focus:border-jungle-500 focus:ring-2 focus:ring-jungle-100 transition-all
                                      @error('p_count') border-red-400 @enderror"
                               oninput="updateTotal()">
                        <p class="text-xs text-gray-400 mt-1">
                            Maximum {{ $remaining }} slot(s) available for this date.
                        </p>
                        @error('p_count')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Number of senior citizens --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">
                            Number of Senior Citizens (20% Discount)
                        </label>
                        <input type="number" id="senior_count" name="senior_count" value="{{ old('senior_count', $existingBooking ? $existingBooking->senior_count : 0) }}"
                               min="0"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm
                                      focus:outline-none focus:border-jungle-500 focus:ring-2 focus:ring-jungle-100 transition-all
                                      @error('senior_count') border-red-400 @enderror"
                               oninput="updateTotal()">
                        <p class="text-xs text-gray-400 mt-1">
                            Senior count cannot exceed total number of persons.
                        </p>
                        @error('senior_count')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Senior ID Images --}}
                    <div id="senior_id_container" class="{{ old('senior_count', $existingBooking ? $existingBooking->senior_count : 0) > 0 ? '' : 'hidden' }}">
                        <label class="block text-sm font-semibold text-gray-600 mb-1.5">
                            Senior Citizen ID Pictures <span class="text-red-400">*</span>
                        </label>
                        
                        <div class="flex items-center justify-between mb-1">
                            <span id="image-count-label" class="text-xs font-semibold text-gray-400">0 / 0</span>
                        </div>
                        <p class="text-gray-400 text-[10px] mb-3 uppercase tracking-wider font-bold">Upload up to <span id="max-images-text">0</span> ID images (max 2MB each).</p>

                        <div id="drop-zone"
                             class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-jungle-500 transition-colors cursor-pointer"
                             onclick="document.getElementById('images-input').click()">
                            <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center" style="background:#d6ece0;">
                                <svg class="w-5 h-5" style="color:#1a3a2a;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 font-medium" id="drop-zone-label">Click to upload ID images</p>
                            <input type="file" id="images-input" multiple accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="handleFiles(this.files)">
                        </div>

                        {{-- Base64 hidden inputs injected here by JS --}}
                        <div id="base64-inputs"></div>

                        {{-- Existing Images (if resuming) --}}
                        @if($existingBooking && $existingBooking->seniorImages->isNotEmpty())
                            <div class="mt-4">
                                <p class="text-[10px] text-gray-400 font-bold uppercase mb-2">Previously Uploaded IDs:</p>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($existingBooking->seniorImages as $image)
                                        <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-100 group">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-cover">
                                            <input type="hidden" name="existing_senior_images[]" value="{{ $image->id }}">
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-[10px] text-white font-bold">Saved</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-[10px] text-jungle-500 mt-2 italic">
                                    Note: Adding new images will be added to your current selection.
                                </p>
                            </div>
                        @endif

                        {{-- Image Previews --}}
                        <div id="image-previews" class="grid grid-cols-4 gap-2 mt-4 hidden"></div>

                        @error('senior_images_base64')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
                        @error('senior_images_base64.*')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
                    </div>

                    {{-- Stripe notice --}}
                    <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-jungle-50 border border-jungle-100">
                        <svg class="w-5 h-5 text-jungle-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-jungle-700">Secure Payment via Stripe</p>
                            <p class="text-xs text-jungle-600/70 mt-0.5">
                                You will be redirected to Stripe's secure checkout to complete your payment.
                            </p>
                        </div>
                    </div>

                    {{-- Terms and Conditions --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="tnc_checkbox" name="tnc" required {{ old('tnc') ? 'checked' : '' }}
                               class="w-4 h-4 text-jungle-500 border-gray-300 rounded focus:ring-jungle-500">
                        <label for="tnc_checkbox" class="text-xs text-gray-500">
                            I agree to the <button type="button" onclick="openTncModal()" class="text-jungle-700 font-semibold underline decoration-jungle-200 underline-offset-2 hover:text-amber-400 transition-colors">Terms and Conditions</button>
                        </label>
                    </div>

                    <button type="submit" id="submit_btn"
                            class="w-full py-3.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-all hover:shadow-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Proceed to Payment
                    </button>
                </form>
            </div>
        </div>

        {{-- Summary Card --}}
        <div class="sm:col-span-2 fade-up" style="animation-delay:.1s;">
            <div class="bg-white rounded-2xl card-shine p-5 sticky top-24">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-4">Booking Summary</p>

                {{-- Tour image --}}
                @if($schedule->tour->images->isNotEmpty())
                <div class="h-32 rounded-xl overflow-hidden mb-4">
                    <img src="{{ asset('storage/' . $schedule->tour->images->first()->image) }}"
                         class="w-full h-full object-cover"
                         alt="{{ $schedule->tour->name }}">
                </div>
                @endif

                <h3 class="font-display font-bold text-jungle-700 text-base leading-tight mb-1">
                    {{ $schedule->tour->name }}
                </h3>
                <p class="text-gray-400 text-xs flex items-center gap-1 mb-4">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    {{ $schedule->tour->location }}
                </p>

                <div class="space-y-2.5 text-sm border-t border-gray-100 pt-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date</span>
                        <span class="font-semibold text-jungle-700 text-xs">
                            {{ \Carbon\Carbon::parse($schedule->date)->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Price/person</span>
                        <span class="font-semibold text-amber-400">
                            ₱{{ number_format($schedule->tour->price, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Persons</span>
                        <span id="summary-pax" class="font-semibold text-jungle-700">1</span>
                    </div>
                    <div id="summary-senior-row" class="flex justify-between hidden">
                        <span class="text-gray-500 italic">Senior Discount (20%)</span>
                        <span id="summary-senior-discount" class="font-semibold text-red-400">-₱0.00</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <span class="font-bold text-gray-700">Total</span>
                        <span id="summary-total" class="font-display font-bold text-amber-400 text-lg">
                            ₱{{ number_format($schedule->tour->price, 2) }}
                        </span>
                    </div>
                </div>

                <p class="text-xs text-gray-400 mt-4 text-center leading-relaxed">
                    Your booking is confirmed once payment is completed on Stripe.
                </p>
            </div>
        </div>

    </div>
</div>

<footer class="mt-16 py-8 border-t border-gray-200 text-center text-xs text-gray-400">
    © {{ date('Y') }} DavaoTours — Proudly showcasing Davao City, Philippines 🇵🇭
</footer>

{{-- TNC Modal --}}
<div id="tnc-modal" class="fixed inset-0 z-50 flex items-center justify-center px-4 hidden">
    <div class="absolute inset-0 bg-jungle-700/40 backdrop-blur-sm" onclick="closeTncModal()"></div>
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl relative animate-[fadeUp_0.3s_ease-out] border border-gray-100">
        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        
        <h3 class="font-display font-bold text-jungle-700 text-2xl mb-4">Terms and Conditions</h3>
        <div class="text-gray-500 text-sm leading-relaxed mb-8 space-y-4">
            <p>Please read our booking rules carefully before proceeding:</p>
            <ul class="list-disc pl-5 space-y-2">
                <li class="font-medium text-jungle-700">No refund if the client cancels the booking.</li>
                <li>Ensure all guest details are accurate before completing the payment.</li>
                <li>Senior citizen discounts require a valid ID verification.</li>
            </ul>
        </div>

        <button onclick="closeTncModal()" 
                class="w-full py-3.5 rounded-xl text-sm font-bold text-white text-center transition-all hover:shadow-lg"
                style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
            I Understand
        </button>
    </div>
</div>

<script>
const pricePerPerson = {{ $schedule->tour->price }};
let imagePool = [];
let maxSeniorImages = 0;

function handleFiles(newFiles) {
    const remaining = maxSeniorImages - imagePool.length;
    if (remaining <= 0) return;

    const accepted = Array.from(newFiles).slice(0, remaining);

    accepted.forEach(file => {
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) return;
        if (file.size > 2 * 1024 * 1024) return;

        const reader = new FileReader();
        reader.onload = e => {
            imagePool.push({ base64: e.target.result });
            updateImageUI();
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('images-input').value = '';
}

function removeImage(index) {
    imagePool.splice(index, 1);
    updateImageUI();
}

function syncBase64Inputs() {
    const container = document.getElementById('base64-inputs');
    container.innerHTML = '';
    imagePool.forEach(item => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'senior_images_base64[]';
        input.value = item.base64;
        container.appendChild(input);
    });
}

function updateImageUI() {
    const count = imagePool.length;
    const pct   = maxSeniorImages > 0 ? (count / maxSeniorImages) * 100 : 0;

    syncBase64Inputs();

    document.getElementById('image-count-label').textContent = count + ' / ' + maxSeniorImages;
    document.getElementById('max-images-text').textContent = maxSeniorImages;

    const label = document.getElementById('drop-zone-label');
    if (maxSeniorImages > 0 && count >= maxSeniorImages) {
        label.textContent = 'Maximum limit reached';
        label.classList.add('text-jungle-700');
    } else {
        label.textContent = 'Click to upload ID images';
        label.classList.remove('text-jungle-700');
    }

    const container = document.getElementById('image-previews');
    container.innerHTML = '';

    if (count > 0) {
        container.classList.remove('hidden');
        imagePool.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-xl overflow-hidden border border-gray-100 group';
            div.innerHTML = `
                <img src="${item.base64}" class="w-full h-full object-cover">
                <button
                    type="button"
                    onclick="removeImage(${index})"
                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/60 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500"
                    title="Remove">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
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

function openTncModal() {
    document.getElementById('tnc-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTncModal() {
    document.getElementById('tnc-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Validation logic
const tncCheckbox = document.getElementById('tnc_checkbox');
const submitBtn = document.getElementById('submit_btn');

function validateForm() {
    if (tncCheckbox.checked) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
}

tncCheckbox.addEventListener('change', validateForm);

// Initial validation call
validateForm();

function updateTotal() {
    const pCountInput = document.getElementById('p_count');
    const seniorCountInput = document.getElementById('senior_count');
    const seniorIdContainer = document.getElementById('senior_id_container');
    
    let pCount = parseInt(pCountInput.value) || 1;
    let seniorCount = parseInt(seniorCountInput.value) || 0;
    
    // Ensure senior count does not exceed total persons
    if (seniorCount > pCount) {
        seniorCount = pCount;
        seniorCountInput.value = pCount;
    }

    maxSeniorImages = seniorCount;
    
    // Toggle ID upload visibility
    if (seniorCount > 0) {
        seniorIdContainer.classList.remove('hidden');
        document.getElementById('summary-senior-row').classList.remove('hidden');
    } else {
        seniorIdContainer.classList.add('hidden');
        document.getElementById('summary-senior-row').classList.add('hidden');
    }
    
    updateImageUI();

    const seniorDiscountPerPerson = pricePerPerson * 0.20;
    const totalDiscount = seniorCount * seniorDiscountPerPerson;
    const totalPrice = (pCount * pricePerPerson) - totalDiscount;
    
    document.getElementById('summary-pax').textContent = pCount;
    document.getElementById('summary-senior-discount').textContent = 
        '-₱' + totalDiscount.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    
    document.getElementById('summary-total').textContent =
        '₱' + totalPrice.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
}

// Initial call to set values correctly on page load (especially when resuming)
updateTotal();
</script>

{{-- Conflict Rejection Modal --}}
@if(session('conflict_booking'))
<div id="conflict-modal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-jungle-700/40 backdrop-blur-sm"></div>
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl relative animate-[fadeUp_0.3s_ease-out] border border-gray-100">
        <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.268 17c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        
        <h3 class="font-display font-bold text-jungle-700 text-2xl mb-2">Scheduling Conflict</h3>
        <p class="text-gray-500 text-sm leading-relaxed mb-6">
            You already have a confirmed booking for <strong class="text-jungle-700">{{ session('conflict_booking')['date'] }}</strong> 
            (<span class="text-amber-400 font-medium">{{ session('conflict_booking')['tour_name'] }}</span>). 
            Please choose a different date or manage your existing schedules.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('client.bookings') }}" 
               class="w-full py-3.5 rounded-xl text-sm font-bold text-white text-center transition-all hover:shadow-lg"
               style="background: linear-gradient(135deg,#c9872a,#e8a83c);">
                View My Bookings
            </a>
            <button onclick="document.getElementById('conflict-modal').remove()" 
                    class="w-full py-3.5 rounded-xl text-sm font-semibold text-gray-500 bg-gray-50 hover:bg-gray-100 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
@endif

</body>
</html>