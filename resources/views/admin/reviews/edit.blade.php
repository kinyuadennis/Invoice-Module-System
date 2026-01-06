@extends('layouts.admin')

@section('title', 'Edit Review')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Review</h1>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.reviews.update', $review->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-input type="text" name="name" label="Reviewer Name" value="{{ old('name', $review->name) }}" required autofocus />
            <x-input type="text" name="title" label="Review Title" value="{{ old('title', $review->title) }}" required />
            
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Review Content <span class="text-red-500">*</span></label>
                <textarea name="content" id="content" rows="4" class="block w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('content', $review->content) }}</textarea>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="rating" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Rating <span class="text-red-500">*</span></label>
                <select name="rating" id="rating" class="block w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    <option value="">Select Rating</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
                @error('rating')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="approved" id="approved" value="1" {{ old('approved', $review->approved) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-[#404040] rounded">
                <label for="approved" class="ml-2 block text-sm text-gray-900">Approved</label>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.reviews.index') }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Update Review</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

