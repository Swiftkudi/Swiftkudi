@extends('layouts.app')

@section('title', 'Seller Profile - Marketplace')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">
        <i class="fas fa-user-edit mr-2 text-blue-400"></i>Seller Profile
    </h1>

    <form method="POST" action="{{ route('marketplace.seller.profile.update') }}" enctype="multipart/form-data" class="bg-dark-800 rounded-2xl p-8 border border-dark-700 space-y-6">
        @csrf

        <!-- Avatar -->
        <div class="flex items-center gap-6">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl flex-shrink-0"
                 id="avatar-preview">
                @if(Auth::user()->marketplace_avatar)
                <img src="{{ asset('storage/' . Auth::user()->marketplace_avatar) }}" alt="Avatar" class="w-full h-full rounded-full object-cover">
                @else
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <label class="btn btn-secondary btn-sm" for="marketplace_avatar">
                    <i class="fas fa-camera mr-2"></i>Change Avatar
                </label>
                <input type="file" name="marketplace_avatar" id="marketplace_avatar" accept="image/*" class="hidden"
                       onchange="previewAvatar(this)">
                <p class="text-gray-500 text-xs mt-1">JPG, PNG, WEBP up to 4MB</p>
            </div>
        </div>

        <!-- Bio -->
        <div>
            <label class="form-label" for="marketplace_bio">Bio</label>
            <textarea name="marketplace_bio" id="marketplace_bio" rows="4"
                      class="w-full px-4 py-3 rounded-lg bg-dark-700 border border-dark-600 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                      placeholder="Tell buyers about yourself...">{{ Auth::user()->marketplace_bio ?? '' }}</textarea>
        </div>

        <!-- Contact Preferences -->
        <div>
            <label class="form-label">Contact Preferences</label>
            <div class="space-y-3">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="marketplace_contact_preferences[email]" value="1"
                           class="w-5 h-5 rounded border-dark-600 text-blue-500 focus:ring-blue-500"
                           {{ (Auth::user()->marketplace_contact_preferences['email'] ?? true) ? 'checked' : '' }}>
                    <span class="text-gray-300">Email notifications for new orders</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="marketplace_contact_preferences[chat]" value="1"
                           class="w-5 h-5 rounded border-dark-600 text-blue-500 focus:ring-blue-500"
                           {{ (Auth::user()->marketplace_contact_preferences['chat'] ?? true) ? 'checked' : '' }}>
                    <span class="text-gray-300">Chat notifications</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="marketplace_contact_preferences[sms]" value="1"
                           class="w-5 h-5 rounded border-dark-600 text-blue-500 focus:ring-blue-500"
                           {{ (Auth::user()->marketplace_contact_preferences['sms'] ?? false) ? 'checked' : '' }}>
                    <span class="text-gray-300">SMS notifications (may incur charges)</span>
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full">
            <i class="fas fa-save mr-2"></i>Save Profile
        </button>
    </form>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').innerHTML =
                '<img src="' + e.target.result + '" class="w-full h-full rounded-full object-cover">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush