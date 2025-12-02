@props([
    'quote',
    'author',
    'business',
    'avatar',
    'role' => null,
    'location' => null,
    'metric' => null,
    'verified' => false,
])

<div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 border border-slate-200 transform hover:-translate-y-1">
    <div class="mb-4">
        <svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
    </div>
    
    <p class="text-slate-700 mb-6 italic text-lg leading-relaxed">"{{ $quote }}"</p>
    
    @if($metric)
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm font-bold text-blue-700">{{ $metric }}</p>
        </div>
    @endif
    
    <div class="flex items-center">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold mr-3 flex-shrink-0">
            {{ $avatar }}
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <p class="font-semibold text-slate-900">{{ $author }}</p>
                @if($verified)
                    <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                @endif
            </div>
            <p class="text-sm text-slate-600">
                @if($role){{ $role }}, @endif{{ $business }}
                @if($location)<span class="text-slate-500"> • {{ $location }}</span>@endif
            </p>
        </div>
    </div>
</div>

