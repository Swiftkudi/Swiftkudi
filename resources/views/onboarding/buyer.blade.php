@extends('layouts.app')

@section('title', 'Select Your Interests')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome to SwiftKudi!</h1>
            <p class="text-gray-600 dark:text-gray-300 mt-2">Select categories you're interested in to personalize your marketplace experience.</p>
        </div>

        <form method="POST" action="{{ route('onboarding.buyer.categories.store') }}" id="categoryForm" enctype="multipart/form-data">
            @csrf

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-8 bg-white dark:bg-dark-900 shadow-sm rounded-3xl border border-gray-200 dark:border-dark-700 p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Student Marketplace Profile</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Tell us about yourself so the campus marketplace can connect you with the right offers.</p>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="name">Full Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', Auth::user()->name) }}" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="phone">Phone Number</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', Auth::user()->phone) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="university_id">University</label>
                            <select id="university_id" name="university_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select your university</option>
                                @foreach($universities as $university)
                                    <option value="{{ $university->id }}" {{ (string)old('university_id', Auth::user()->university_id) === (string)$university->id ? 'selected' : '' }}>{{ $university->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="campus">Campus / Hall</label>
                            <input id="campus" name="campus" type="text" value="{{ old('campus', Auth::user()->campus) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="year_of_study">Year of Study</label>
                            <input id="year_of_study" name="year_of_study" type="text" value="{{ old('year_of_study', Auth::user()->year_of_study) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="faculty">Faculty / Department</label>
                            <input id="faculty" name="faculty" type="text" value="{{ old('faculty', Auth::user()->faculty) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="marketplace_avatar">Profile Photo</label>
                            <input id="marketplace_avatar" name="marketplace_avatar" type="file" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-900 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-200" onchange="previewAvatar(this)">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="marketplace_bio">Tell buyers what you need</label>
                        <textarea id="marketplace_bio" name="marketplace_bio" rows="4"
                                  class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Share what you're looking for or what you want to buy on campus">{{ old('marketplace_bio', Auth::user()->marketplace_bio) }}</textarea>
                    </div>

                    <div class="space-y-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Contact Preferences</div>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="marketplace_contact_preferences[email]" value="1"
                                   class="w-5 h-5 rounded border-gray-300 dark:border-dark-700 text-indigo-600"
                                   {{ old('marketplace_contact_preferences.email', $contactPreferences['email']) ? 'checked' : '' }}>
                            <span class="text-gray-600 dark:text-gray-300">Email notifications</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="marketplace_contact_preferences[chat]" value="1"
                                   class="w-5 h-5 rounded border-gray-300 dark:border-dark-700 text-indigo-600"
                                   {{ old('marketplace_contact_preferences.chat', $contactPreferences['chat']) ? 'checked' : '' }}>
                            <span class="text-gray-600 dark:text-gray-300">Marketplace chat notifications</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="marketplace_contact_preferences[sms]" value="1"
                                   class="w-5 h-5 rounded border-gray-300 dark:border-dark-700 text-indigo-600"
                                   {{ old('marketplace_contact_preferences.sms', $contactPreferences['sms']) ? 'checked' : '' }}>
                            <span class="text-gray-600 dark:text-gray-300">SMS alerts (optional)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Professional Services -->
            @if($professionalCategories->count() > 0)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Professional Services</h2>
                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" onclick="toggleAll('professional', true)">Select All</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($professionalCategories as $category)
                    <label class="relative flex items-start p-4 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors category-card" data-category-type="professional">
                        <input type="checkbox" 
                               name="categories[]" 
                               value="{{ $category->id }}" 
                               class="sr-only professional-checkbox"
                               {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                        <div class="flex-1">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                            @if($category->description)
                            <span class="block text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->description }}</span>
                            @endif
                        </div>
                        <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-dark-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Digital Products -->
            @if($digitalCategories->count() > 0)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Digital Products</h2>
                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" onclick="toggleAll('digital', true)">Select All</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($digitalCategories as $category)
                    <label class="relative flex items-start p-4 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors category-card" data-category-type="digital">
                        <input type="checkbox" 
                               name="categories[]" 
                               value="{{ $category->id }}" 
                               class="sr-only digital-checkbox"
                               {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                        <div class="flex-1">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                            @if($category->description)
                            <span class="block text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->description }}</span>
                            @endif
                        </div>
                        <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-dark-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Growth Marketplace -->
            @if($growthCategories->count() > 0)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Growth Marketplace</h2>
                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" onclick="toggleAll('growth', true)">Select All</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($growthCategories as $category)
                    <label class="relative flex items-start p-4 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors category-card" data-category-type="growth">
                        <input type="checkbox" 
                               name="categories[]" 
                               value="{{ $category->id }}" 
                               class="sr-only growth-checkbox"
                               {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                        <div class="flex-1">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                            @if($category->description)
                            <span class="block text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->description }}</span>
                            @endif
                        </div>
                        <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-dark-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Jobs -->
            @if($jobCategories->count() > 0)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Jobs</h2>
                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" onclick="toggleAll('job', true)">Select All</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($jobCategories as $category)
                    <label class="relative flex items-start p-4 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors category-card" data-category-type="job">
                        <input type="checkbox" 
                               name="categories[]" 
                               value="{{ $category->id }}" 
                               class="sr-only job-checkbox"
                               {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                        <div class="flex-1">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                            @if($category->description)
                            <span class="block text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->description }}</span>
                            @endif
                        </div>
                        <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-dark-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- No categories message -->
            @if($professionalCategories->isEmpty() && $digitalCategories->isEmpty() && $growthCategories->isEmpty() && $jobCategories->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No categories available at the moment.</p>
                    <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">Go to Dashboard</a>
                </div>
            @else
                <div class="flex items-center justify-between mt-8 pt-4 border-t border-gray-200 dark:border-dark-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span id="selectedCount">0</span> categories selected
                    </div>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors">
                        Continue to Dashboard
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('input[name="categories[]"]:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
}

function updateVisualState(checkbox) {
    const card = checkbox.closest('.category-card');
    const checkIcon = card.querySelector('.check-icon');
    const checkDiv = card.querySelector('.absolute');
    
    if (checkbox.checked) {
        card.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900');
        checkIcon.classList.remove('hidden');
        checkDiv.classList.add('border-indigo-500', 'bg-indigo-500');
    } else {
        card.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900');
        checkIcon.classList.add('hidden');
        checkDiv.classList.remove('border-indigo-500', 'bg-indigo-500');
    }
    
    updateSelectedCount();
}

function toggleAll(type, checked) {
    const selector = type + '-checkbox';
    const checkboxes = document.querySelectorAll('.' + selector);
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = checked;
        updateVisualState(checkbox);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="categories[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateVisualState(this);
        });
        updateVisualState(checkbox);
    });
    
    updateSelectedCount();
});
</script>

<style>
.category-card input:checked + div .check-icon {
    display: block;
}
.category-card input:checked ~ .absolute {
    background-color: #4f46e5;
    border-color: #4f46e5;
}
</style>
@endsection
