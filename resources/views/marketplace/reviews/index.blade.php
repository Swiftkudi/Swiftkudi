@extends('layouts.app')

@section('title', 'My Reviews - Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-star mr-2 text-yellow-400"></i>My Reviews
    </h1>

    @if($reviews->isNotEmpty())
    <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="bg-dark-800 rounded-2xl p-6 border border-dark-700">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr($review->reviewer->name ?? 'Unknown', 0, 1)) }}
                </div>
                <div>
                    <p class="text-white font-medium">{{ $review->reviewer->name ?? 'Unknown' }}</p>
                    <p class="text-gray-500 text-xs">
                        {{ $review->created_at->diffForHumans() }} •
                        Reviewed for: {{ $review->reviewed->name ?? 'Unknown' }}
                    </p>
                </div>
                <div class="ml-auto flex items-center text-yellow-400">
                    @for($i = 0; $i < 5; $i++)
                        <i class="fas fa-star {{ $i < $review->rating ? '' : 'far' }} text-sm"></i>
                    @endfor
                    <span class="text-gray-400 text-sm ml-2">({{ $review->rating }}/5)</span>
                </div>
            </div>

            @if($review->comment)
            <p class="text-gray-300">{{ $review->comment }}</p>
            @endif

            @if($review->images)
            <div class="flex gap-3 mt-3">
                @foreach($review->images as $image)
                <img src="{{ asset('storage/' . $image) }}" alt="Review image"
                     class="w-24 h-24 object-cover rounded-lg"
                     onerror="this.src='https://via.placeholder.com/96x96/1e293b/475569?text=N/A'">
                @endforeach
            </div>
            @endif

            <div class="mt-3 flex items-center gap-2 text-sm text-gray-500">
                <span class="{{ $review->is_approved ? 'text-green-400' : 'text-yellow-400' }}">
                    <i class="fas fa-{{ $review->is_approved ? 'check-circle' : 'clock' }}"></i>
                    {{ $review->is_approved ? 'Approved' : 'Pending Approval' }}
                </span>
                @if($review->order)
                • Order #{{ $review->order->id }}
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex justify-center">
        {{ $reviews->appends(request()->query())->links() }}
    </div>
    @else
    <div class="text-center py-20 bg-dark-800 rounded-2xl border border-dark-700">
        <i class="fas fa-star text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-xl text-gray-400 mb-2">No reviews yet</h3>
        <p class="text-gray-500 mb-6">Write reviews for orders you've completed.</p>
        <a href="{{ route('marketplace.listings.index') }}" class="btn btn-primary">
            <i class="fas fa-shopping-cart mr-2"></i>Browse Marketplace
        </a>
    </div>
    @endif
</div>
@endsection