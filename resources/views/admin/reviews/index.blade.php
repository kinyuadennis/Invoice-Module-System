@extends('layouts.admin')

@section('title', 'Reviews')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reviews</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage testimonials and reviews</p>
        </div>
        <a href="{{ route('admin.reviews.create') }}">
            <x-button variant="primary">Add Review</x-button>
        </a>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <x-select 
                name="approved" 
                label="Status" 
                :options="[
                    ['value' => '', 'label' => 'All Reviews'],
                    ['value' => '1', 'label' => 'Approved'],
                    ['value' => '0', 'label' => 'Pending']
                ]" 
                value="{{ request('approved') }}" 
            />
            <x-select 
                name="rating" 
                label="Rating" 
                :options="array_merge([['value' => '', 'label' => 'All Ratings']], collect(range(5, 1))->map(fn($r) => ['value' => $r, 'label' => $r . ' Star' . ($r > 1 ? 's' : '')])->toArray())" 
                value="{{ request('rating') }}" 
            />
            <x-input 
                type="text" 
                name="search" 
                label="Search" 
                value="{{ request('search') }}" 
                placeholder="Name, title, or content..."
            />
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    @if(isset($reviews) && $reviews->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($reviews as $review)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $review['name'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ Str::limit($review['title'], 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                                <span class="ml-1 text-gray-600 dark:text-gray-300">({{ $review['rating'] }})</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($review['approved'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $review['created_at']->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.reviews.edit', $review['id']) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <form method="POST" action="{{ route('admin.reviews.approve', $review['id']) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-{{ $review['approved'] ? 'yellow' : 'green' }}-600 hover:text-{{ $review['approved'] ? 'yellow' : 'green' }}-900">
                                    {{ $review['approved'] ? 'Unapprove' : 'Approve' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No reviews found</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

