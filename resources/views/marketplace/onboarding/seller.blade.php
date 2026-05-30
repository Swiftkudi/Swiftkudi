@extends('layouts.app')

@section('title', 'Student Seller Onboarding')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Student Seller Onboarding</h1>
            <p class="text-gray-600 dark:text-gray-300 mt-2">Set up your student marketplace seller profile and tell buyers what you offer.</p>
        </div>

        <form method="POST" action="{{ route('marketplace.onboarding.seller.store') }}" enctype="multipart/form-data">
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

            <div class="bg-white dark:bg-dark-900 shadow-sm rounded-3xl border border-gray-200 dark:border-dark-700 p-6 space-y-6">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Profile Details</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">This information appears to student buyers in the campus marketplace.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="name">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', Auth::user()->name) }}" required
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="phone">Phone Number</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', Auth::user()->phone) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="university_id">University</label>
                        <select id="university_id" name="university_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select your university</option>
                            @foreach($universities as $university)
                                <option value="{{ $university->id }}" {{ (string) old('university_id', Auth::user()->university_id) === (string) $university->id ? 'selected' : '' }}>{{ $university->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="campus">Campus / Hall</label>
                        <input id="campus" name="campus" type="text" value="{{ old('campus', Auth::user()->campus) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="year_of_study">Year of Study</label>
                        <input id="year_of_study" name="year_of_study" type="text" value="{{ old('year_of_study', Auth::user()->year_of_study) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="faculty">Faculty / Department</label>
                        <input id="faculty" name="faculty" type="text" value="{{ old('faculty', Auth::user()->faculty) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="marketplace_avatar">Profile Photo</label>
                        <input id="marketplace_avatar" name="marketplace_avatar" type="file" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-900 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-200" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="marketplace_bio">Describe your student service or product</label>
                    <textarea id="marketplace_bio" name="marketplace_bio" rows="5"
                              class="mt-1 block w-full rounded-lg border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Tell buyers what you offer and why you’re the right choice.">{{ old('marketplace_bio', Auth::user()->marketplace_bio) }}</textarea>
                </div>

                <div class="space-y-3">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Contact Preferences</div>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="marketplace_contact_preferences[email]" value="1"
                               class="w-5 h-5 rounded border-gray-300 dark:border-dark-700 text-indigo-600"
                               {{ old('marketplace_contact_preferences.email', $contactPreferences['email']) ? 'checked' : '' }} />
                        <span class="text-gray-600 dark:text-gray-300">Email buyers</span>
                    </label>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="marketplace_contact_preferences[chat]" value="1"
                               class="w-5 h-5 rounded border-gray-300 dark:border-dark-700 text-indigo-600"
                               {{ old('marketplace_contact_preferences.chat', $contactPreferences['chat']) ? 'checked' : '' }} />
                        <span class="text-gray-600 dark:text-gray-300">In-app chat</span>
                    </label>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="marketplace_contact_preferences[sms]" value="1"
                               class="w-5 h-5 rounded border-gray-300 dark:border-dark-700 text-indigo-600"
                               {{ old('marketplace_contact_preferences.sms', $contactPreferences['sms']) ? 'checked' : '' }} />
                        <span class="text-gray-600 dark:text-gray-300">SMS alerts (optional)</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-dark-700 text-right">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors">Save seller profile</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
