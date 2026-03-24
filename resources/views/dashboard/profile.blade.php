@extends('layouts.app')

@section('title', 'My Profile - SwiftKudi')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
            <p class="text-gray-600 mt-1">Manage your account settings</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-24"></div>
                    <div class="px-6 pb-6">
                        <div class="relative -mt-12 mb-4">
                            <div class="w-24 h-24 bg-white rounded-full p-1 shadow-lg">
                                <div class="w-full h-full bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 text-3xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-500 text-sm">{{ $user->email }}</p>

                        <!-- Level Badge -->
                        <div class="mt-4 flex items-center">
                            <span class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">
                                <i class="fas fa-star mr-1"></i> Level {{ $user->level }}
                            </span>
                            <span class="ml-2 text-sm text-gray-500">{{ number_format($user->experience_points) }} XP</span>
                        </div>

                        <!-- Streak -->
                        @if($user->daily_streak > 0)
                            <div class="mt-3 flex items-center">
                                <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-fire mr-1"></i> {{ $user->daily_streak }} Day Streak
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Badges -->
                <div class="bg-white rounded-lg shadow mt-6 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Your Badges</h3>
                    @if($user->badges && $user->badges->count() > 0)
                        <div class="flex flex-wrap gap-3">
                            @foreach($user->badges as $userBadge)
                                <div class="flex flex-col items-center" title="{{ $userBadge->badge->description }}">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-1">
                                        <i class="fas fa-medal text-yellow-600 text-xl"></i>
                                    </div>
                                    <span class="text-xs text-gray-600 text-center">{{ $userBadge->badge->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm text-center py-4">Complete tasks to earn badges!</p>
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['tasks_completed'] }}</p>
                        <p class="text-sm text-gray-500">Tasks Done</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['tasks_created'] }}</p>
                        <p class="text-sm text-gray-500">Tasks Created</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">₦{{ number_format($stats['total_earned'], 0) }}</p>
                        <p class="text-sm text-gray-500">Total Earned</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $user->level }}</p>
                        <p class="text-sm text-gray-500">Level</p>
                    </div>
                </div>

                <!-- XP Progress -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-bold text-gray-900">Level Progress</h3>
                        <span class="text-sm text-gray-500">Level {{ $user->level }} → Level {{ $user->level + 1 }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                        @php
                            $progress = $user->getXpProgress();
                            $progressPercentage = max(0, min(100, (int) $progress['percentage']));
                        @endphp
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-4 rounded-full transition-all profile-progress-bar" data-progress="{{ $progressPercentage }}"></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>{{ number_format($progress['xp_current']) }} XP</span>
                        <span>{{ number_format($progress['xp_needed']) }} XP needed</span>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="font-bold text-gray-900 mb-4">Edit Profile</h3>
                    <form action="{{ route('dashboard.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                    placeholder="+234..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" value="{{ $user->email }}" disabled
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                                <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Referral Code -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Your Referral Code</h3>
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4">
                        <div>
                            <p class="text-2xl font-bold text-indigo-600">{{ $user->referral_code }}</p>
                            <p class="text-sm text-gray-500">Share this code and earn ₦500 per referral!</p>
                        </div>
                        <button onclick="copyReferralCode()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                            <i class="fas fa-copy mr-2"></i> Copy
                        </button>
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="bg-white rounded-lg shadow p-6 mt-6 border border-red-100">
                    <h3 class="font-bold text-red-700 mb-2">Danger Zone</h3>
                    <p class="text-sm text-gray-600 mb-4">You can permanently delete your account here. This action cannot be undone.</p>
                    <form action="{{ route('dashboard.profile.delete') }}" method="POST" onsubmit="return confirm('Delete your account permanently? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                            <i class="fas fa-trash mr-2"></i>Delete My Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyReferralCode() {
    const code = '{{ $user->referral_code }}';
    navigator.clipboard.writeText(code).then(() => {
        alert('Referral code copied to clipboard!');
    }).catch(() => {
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Referral code copied to clipboard!');
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.profile-progress-bar').forEach(function (bar) {
        bar.style.width = (bar.dataset.progress || '0') + '%';
    });
});
</script>
@endpush
@endsection
