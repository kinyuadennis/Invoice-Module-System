@props([
    'caseStudies' => [],
    'title' => 'Success Stories',
    'subtitle' => 'See how Kenyan businesses are growing with InvoiceHub',
])

@if(!empty($caseStudies))
<section class="py-16 lg:py-24 bg-slate-50" id="case-studies">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                {{ $title }}
            </h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                {{ $subtitle }}
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @foreach($caseStudies as $index => $study)
                <div class="bg-white rounded-xl shadow-lg p-8 border border-slate-200 hover:shadow-xl transition-all">
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-lg">{{ substr($study['company'], 0, 1) }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $study['company'] }}</h3>
                                <p class="text-sm text-slate-500">{{ $study['industry'] }} • {{ $study['location'] }}</p>
                            </div>
                        </div>
                        
                        <h4 class="text-xl font-bold text-slate-900 mb-4">
                            {{ $study['title'] }}
                        </h4>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        <div>
                            <h5 class="font-semibold text-slate-900 mb-2">Challenge</h5>
                            <p class="text-slate-600 text-sm">{{ $study['challenge'] }}</p>
                        </div>
                        
                        <div>
                            <h5 class="font-semibold text-slate-900 mb-2">Solution</h5>
                            <p class="text-slate-600 text-sm">{{ $study['solution'] }}</p>
                        </div>
                        
                        <div>
                            <h5 class="font-semibold text-slate-900 mb-2">Results</h5>
                            <ul class="space-y-2">
                                @foreach($study['results'] as $result)
                                    <li class="flex items-start text-sm text-slate-600">
                                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>{{ $result }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    <div class="border-t border-slate-200 pt-6">
                        <blockquote class="mb-4">
                            <p class="text-slate-700 italic">"{{ $study['quote'] }}"</p>
                        </blockquote>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $study['author'] }}</p>
                                <p class="text-sm text-slate-600">{{ $study['role'] }}</p>
                            </div>
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= ($study['rating'] ?? 5) ? 'text-yellow-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- CTA after case studies -->
        <div class="text-center mt-12">
            <a 
                href="{{ route('register') }}"
                class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105"
                onclick="if(typeof gtag !== 'undefined') gtag('event', 'click', {'event_category': 'CTA', 'event_label': 'Case Studies - Get Started'});"
            >
                Start Your Success Story
            </a>
        </div>
    </div>
</section>
@endif

