@extends('layouts.app')

@section('title', 'My Reviews - Seller Dashboard')

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
                <div class="flex-1">
                    <p class="text-white font-medium">{{ $review->reviewer->name ?? 'Unknown' }}</p>
                    <p class="text-gray-500 text-xs">{{ $review->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center text-yellow-400">
                    @for($i = 0; $i < 5; $i++)
                        <i class="fas fa-star {{ $i < $review->rating ? '' : 'far' }} text-sm"></i>
                    @endfor
                    <span class="text-gray-400 text-sm ml-2">{{ $review->rating }}/5</span>
                </div>
                @if(!$review->is_approved)
                <span class="bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded-full text-xs font-medium">
                    Pending
                </span>
                @endif
            </div>

            @if($review->comment)
            <p class="text-gray-300">{{ $review->comment }}</p>
            @endif

            @if($review->images)
            <div class="flex gap-3 mt-3">
                @foreach($review->images as $image)
                <img src="{{ asset('storage/' . $image) }}" alt="Review image"
                     class="w-20 h-20 object-cover rounded-lg"
                     onerror="this.src='https://via.placeholder.com/80x80/1e293b/475569?text=N/A'">
                @endforeach
            </div>
            @endif
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
        <p class="text-gray-500">Reviews from buyers will appear here once they review your orders.</p>
    </div>
    @endif
</div>
@endsection