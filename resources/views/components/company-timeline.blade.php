@props([
    'timeline' => [],
])

@if(count($timeline) > 0)
    <div class="relative">
        <!-- Timeline line -->
        <div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-slate-200 transform md:-translate-x-1/2"></div>
        
        <div class="space-y-8">
            @foreach($timeline as $index => $item)
                <div class="relative flex flex-col md:flex-row md:items-center gap-4">
                    <!-- Timeline dot -->
                    <div class="absolute left-4 md:left-1/2 w-4 h-4 bg-blue-500 rounded-full border-4 border-white shadow-lg transform md:-translate-x-1/2 z-10"></div>
                    
                    <!-- Content -->
                    <div class="ml-12 md:ml-0 md:w-1/2 {{ $index % 2 === 0 ? 'md:pr-8 md:text-right' : 'md:ml-auto md:pl-8 md:text-left' }}">
                        <div class="bg-white rounded-lg p-6 shadow-md border border-slate-200 hover:shadow-lg transition-shadow">
                            <div class="text-blue-600 font-bold text-lg mb-2">{{ $item['year'] ?? '' }}</div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $item['title'] ?? '' }}</h3>
                            <p class="text-slate-600 leading-relaxed">{{ $item['description'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

