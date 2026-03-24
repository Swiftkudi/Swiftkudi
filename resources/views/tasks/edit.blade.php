@extends('layouts.app')

@section('title', 'Edit Task - SwiftKudi')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Edit Task</h1>
            <p class="text-sm text-gray-500 mt-1">Modify task details (budget/quantity cannot be changed here).</p>
        </div>

        <form action="{{ route('tasks.update', $task) }}" method="POST" class="bg-white rounded-xl p-6 border border-gray-100">
            @csrf
            @method('PUT')

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" class="mt-1 block w-full rounded-md border-gray-200">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-200">{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Proof Type</label>
                <select name="proof_type" class="mt-1 block w-full rounded-md border-gray-200">
                    @foreach(array_values(array_unique(\App\Models\Task::PROOF_TYPES)) as $pt)
                        <option value="{{ $pt }}" {{ (old('proof_type', $task->proof_type) === $pt) ? 'selected' : '' }}>{{ ucfirst($pt) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('tasks.my-tasks') }}" class="px-4 py-2 rounded bg-gray-100">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
