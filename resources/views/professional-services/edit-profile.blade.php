@extends('layouts.app')

@section('title', 'Edit Provider Profile - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('professional-services.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center gap-2 mb-4">
                <i class="fas fa-arrow-left"></i> Back to Services
            </a>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Edit Provider Profile</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your professional service provider profile</p>
        </div>

        <form id="profile-form" action="{{ route('professional-services.update-profile') }}" method="POST" class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 p-6">
            @csrf
            @method('PUT')

            <!-- Availability Status -->
            <div class="mb-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_available" value="1" {{ $profile->is_available ? 'checked' : '' }} 
                        class="w-5 h-5 rounded border-gray-300 dark:border-dark-600 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">Available for Work</span>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Show clients that you're accepting new projects</p>
                    </div>
                </label>
            </div>

            <!-- Hourly Rate -->
            <div class="mb-6">
                <label for="hourly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Hourly Rate (₦)
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">₦</span>
                    <input type="number" name="hourly_rate" id="hourly_rate" value="{{ $profile->hourly_rate }}"
                        class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g., 5000" min="0">
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Set your hourly rate for clients to see</p>
            </div>

            <!-- Bio -->
            <div class="mb-6">
                <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Bio / About
                </label>
                <textarea name="bio" id="bio" rows="4"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                    placeholder="Tell clients about yourself, your experience, and what makes you unique...">{{ $profile->bio }}</textarea>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Max 1000 characters</p>
            </div>

            <!-- Skills -->
            <div class="mb-6">
                <label for="skills" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Skills
                </label>
                <div id="skills-container" class="flex flex-wrap gap-2 mb-2">
                    @php
                        $skillsArray = is_array($profile->skills) ? $profile->skills : (json_decode($profile->skills, true) ?: []);
                    @endphp
                    @if($skillsArray)
                        @foreach($skillsArray as $skill)
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-full text-sm">
                                {{ $skill }}
                                <button type="button" onclick="removeSkill(this)" class="hover:text-indigo-900 dark:hover:text-indigo-100">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        @endforeach
                    @endif
                </div>
                <div class="flex gap-2">
                    <input type="text" id="skill-input"
                        class="flex-1 px-4 py-2 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Add a skill (e.g., Web Design, Copywriting)">
                    <button type="button" onclick="addSkill()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                        Add
                    </button>
                </div>
                <input type="hidden" name="skills" id="skills-input" value="">
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter each skill individually using the Add button</p>
            </div>

            <!-- Portfolio Links -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Portfolio Links
                </label>
                <div id="portfolio-container" class="space-y-2 mb-2">
                    @php
                        $portfolioArray = is_array($profile->portfolio_links) ? $profile->portfolio_links : (json_decode($profile->portfolio_links, true) ?: []);
                    @endphp
                    @if($portfolioArray)
                        @foreach($portfolioArray as $link)
                            <div class="flex gap-2">
                                <input type="url" 
                                    class="portfolio-link-input flex-1 px-4 py-2 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    value="{{ $link }}"
                                    placeholder="https://example.com/your-work">
                                <button type="button" onclick="removePortfolioLink(this)"
                                    class="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" onclick="addPortfolioLink()"
                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm flex items-center gap-1">
                    <i class="fas fa-plus"></i> Add Portfolio Link
                </button>
                <input type="hidden" name="portfolio_links" id="portfolio-input" value="">
            </div>

            <!-- Certifications -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Certifications
                </label>
                <div id="certifications-container" class="space-y-2 mb-2">
                    @php
                        $certsArray = is_array($profile->certifications) ? $profile->certifications : (json_decode($profile->certifications, true) ?: []);
                    @endphp
                    @if($certsArray)
                        @foreach($certsArray as $cert)
                            <div class="flex gap-2">
                                <input type="text" 
                                    class="certification-input flex-1 px-4 py-2 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    value="{{ $cert }}"
                                    placeholder="e.g., Google Certified Digital Marketer">
                                <button type="button" onclick="removeCertification(this)"
                                    class="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" onclick="addCertification()"
                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm flex items-center gap-1">
                    <i class="fas fa-plus"></i> Add Certification
                </button>
                <input type="hidden" name="certifications" id="certifications-input" value="">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-save mr-2"></i> Save Profile
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Handle form submission with AJAX
    document.getElementById('profile-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Update inputs before submitting
        updateSkillsInput();
        updatePortfolioInput();
        updateCertificationsInput();
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(new FormData(form)))
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show success message using Swal or alert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert(data.message);
                }
                // Optionally redirect or refresh
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                // Show error message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update profile'
                    });
                } else {
                    alert(data.message || 'Failed to update profile');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            } else {
                alert('An error occurred. Please try again.');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    // Skills management
    function addSkill() {
        const input = document.getElementById('skill-input');
        const skill = input.value.trim();
        if (!skill) return;

        const container = document.getElementById('skills-container');
        const span = document.createElement('span');
        span.className = 'inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-full text-sm';
        span.innerHTML = `
            ${skill}
            <button type="button" onclick="removeSkill(this)" class="hover:text-indigo-900 dark:hover:text-indigo-100">
                <i class="fas fa-times text-xs"></i>
            </button>
        `;
        container.appendChild(span);
        input.value = '';
        updateSkillsInput();
    }

    function removeSkill(btn) {
        btn.parentElement.remove();
        updateSkillsInput();
    }

    function updateSkillsInput() {
        const skills = [];
        document.querySelectorAll('#skills-container span').forEach(span => {
            // Get text content without the remove button text
            const text = span.childNodes[0].textContent.trim();
            if (text) skills.push(text);
        });
        // Store as JSON array string - backend will handle it
        document.getElementById('skills-input').value = JSON.stringify(skills);
    }

    // Portfolio links management
    function addPortfolioLink() {
        const container = document.getElementById('portfolio-container');
        const div = document.createElement('div');
        div.className = 'flex gap-2';
        div.innerHTML = `
            <input type="url" 
                class="portfolio-link-input flex-1 px-4 py-2 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="https://example.com/your-work">
            <button type="button" onclick="removePortfolioLink(this)"
                class="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function removePortfolioLink(btn) {
        btn.parentElement.remove();
        updatePortfolioInput();
    }

    function updatePortfolioInput() {
        const links = [];
        document.querySelectorAll('.portfolio-link-input').forEach(input => {
            if (input.value.trim()) links.push(input.value.trim());
        });
        document.getElementById('portfolio-input').value = JSON.stringify(links);
    }

    // Certifications management
    function addCertification() {
        const container = document.getElementById('certifications-container');
        const div = document.createElement('div');
        div.className = 'flex gap-2';
        div.innerHTML = `
            <input type="text" 
                class="certification-input flex-1 px-4 py-2 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., Google Certified Digital Marketer">
            <button type="button" onclick="removeCertification(this)"
                class="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function removeCertification(btn) {
        btn.parentElement.remove();
        updateCertificationsInput();
    }

    function updateCertificationsInput() {
        const certs = [];
        document.querySelectorAll('.certification-input').forEach(input => {
            if (input.value.trim()) certs.push(input.value.trim());
        });
        document.getElementById('certifications-input').value = JSON.stringify(certs);
    }

    // Initialize inputs on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSkillsInput();
        updatePortfolioInput();
        updateCertificationsInput();
        
        // Add input listeners for real-time updates
        document.querySelectorAll('.portfolio-link-input').forEach(input => {
            input.addEventListener('input', updatePortfolioInput);
        });
        document.querySelectorAll('.certification-input').forEach(input => {
            input.addEventListener('input', updateCertificationsInput);
        });
    });

    // Handle Enter key for skills input
    document.getElementById('skill-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSkill();
        }
    });
</script>
@endpush
@endsection
