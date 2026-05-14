@extends('layouts.app')

@section('title', 'Write a Review')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('marketplace.orders.show', $order->id) }}" class="text-blue-400 hover:underline mb-4 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>Back to Order
    </a>

    <div class="bg-dark-800 rounded-2xl p-8 border border-dark-700">
        <h1 class="text-2xl font-bold text-white mb-2">
            <i class="fas fa-pen mr-2 text-yellow-400"></i>Write a Review
        </h1>
        <p class="text-gray-400 mb-6">
            For order #{{ $order->id }} — {{ $order->listing->title }} from {{ $order->seller->name }}
        </p>

        <form method="POST" action="{{ route('marketplace.reviews.store', $order->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Rating -->
            <div>
                <label class="form-label">Rating *</label>
                <div class="flex gap-3 mt-2" id="rating-selector">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" data-rating="{{ $i }}"
                            class="rating-star w-12 h-12 rounded-full border-2 border-gray-600 flex items-center justify-center text-2xl cursor-pointer transition-all hover:border-yellow-400 hover:bg-yellow-400/10"
                            onclick="selectRating({{ $i }})">
                        <i class="far fa-star text-yellow-400" id="star-{{ $i }}"></i>
                    </button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="rating-input" value="0" required>
                @error('rating')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Comment -->
            <div>
                <label class="form-label" for="comment">Your Review</label>
                <textarea name="comment" id="comment" rows="5"
                          class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                          placeholder="Share your experience with this purchase..."
                          maxlength="1000">{{ old('comment') }}</textarea>
                @error('comment')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Images -->
            <div>
                <label class="form-label">Photos (optional)</label>
                <div class="border-2 border-dashed border-dark-600 rounded-2xl p-8 text-center hover:border-blue-500 transition-colors"
                     onclick="document.getElementById('review-images').click()">
                    <i class="fas fa-camera text-3xl text-gray-500 mb-2"></i>
                    <p class="text-gray-400 text-sm">Upload up to 5 photos</p>
                </div>
                <input type="file" name="images[]" multiple accept="image/*" id="review-images" class="hidden"
                       onchange="handleReviewPreview(this)">
                <div id="review-preview" class="grid grid-cols-4 gap-3 mt-4"></div>
                @error('images')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <i class="fas fa-paper-plane mr-2"></i>Submit Review
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
let selectedRating = 0;

function selectRating(rating) {
    selectedRating = rating;
    document.getElementById('rating-input').value = rating;

    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star-' + i);
        const btn = document.getElementById('rating-selector').querySelector(`[data-rating="${i}"]`);
        if (i <= rating) {
            star.className = 'fas fa-star text-yellow-400';
            btn.classList.add('bg-yellow-400/20', 'border-yellow-400');
            btn.classList.remove('border-gray-600');
        } else {
            star.className = 'far fa-star text-yellow-400';
            btn.classList.remove('bg-yellow-400/20', 'border-yellow-400');
            btn.classList.add('border-gray-600');
        }
    }
}

function handleReviewPreview(input) {
    const preview = document.getElementById('review-preview');
    preview.innerHTML = '';
    Array.from(input.files).slice(0, 5).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-lg overflow-hidden bg-dark-700';
            div.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush