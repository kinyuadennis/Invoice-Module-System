@props([
    'title' => 'Trusted by Kenyan Businesses',
    'subtitle' => 'See how InvoiceHub helps businesses get paid faster',
])

<section class="py-16 lg:py-24 bg-slate-50" x-data="reviewsManager('{{ route('api.reviews') }}')" x-init="loadReviews()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                {{ $title }}
            </h2>
            @if($subtitle)
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
        
        <!-- Loading State -->
        <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
            <template x-for="i in 4" :key="i">
                <div class="bg-white rounded-xl p-6 shadow-md border border-slate-200 animate-pulse">
                    <div class="h-4 bg-slate-200 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-slate-200 rounded w-full mb-2"></div>
                    <div class="h-4 bg-slate-200 rounded w-5/6 mb-6"></div>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-slate-200 rounded-full mr-3"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-slate-200 rounded w-1/2 mb-2"></div>
                            <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Reviews Grid -->
        <div x-show="!loading && reviews.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
            <template x-for="review in reviews" :key="review.id">
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 border border-slate-200 transform hover:-translate-y-1">
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                        <div class="flex items-center">
                            <template x-for="i in 5" :key="i">
                                <svg class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </template>
                        </div>
                    </div>
                    
                    <p class="text-slate-700 mb-4 italic text-lg leading-relaxed" x-text="'\"' + review.content + '\"'"></p>
                    
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold mr-3 flex-shrink-0" x-text="getInitials(review.name)"></div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-slate-900" x-text="review.name"></p>
                                <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p class="text-sm text-slate-600" x-text="review.title"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Empty State -->
        <div x-show="!loading && reviews.length === 0" class="text-center py-12">
            <p class="text-slate-600">No reviews available at the moment.</p>
        </div>
        
        <!-- CTA after testimonials -->
        <div class="text-center mt-12">
            <a 
                href="{{ route('register') }}"
                class="inline-flex items-center justify-center px-8 py-4 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105"
            >
                Join 500+ Businesses
            </a>
        </div>
    </div>
    
    <!-- Schema.org JSON-LD for SEO -->
    <script type="application/ld+json" x-show="reviews.length > 0" x-html="getSchemaJson()"></script>
</section>

@verbatim
<script>
function reviewsManager(apiUrl) {
    return {
        reviews: [],
        loading: true,
        apiUrl: apiUrl,
        
        async loadReviews() {
            try {
                this.loading = true;
                const response = await fetch(this.apiUrl + '?approved=1&limit=8');
                const data = await response.json();
                this.reviews = data.reviews || [];
            } catch (error) {
                console.error('Error loading reviews:', error);
                this.reviews = [];
            } finally {
                this.loading = false;
            }
        },
        
        getInitials(name) {
            if (!name) return '??';
            const words = name.trim().split(' ');
            if (words.length === 0) return '??';
            if (words.length === 1) return words[0].charAt(0).toUpperCase();
            return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
        },
        
        getSchemaJson() {
            if (this.reviews.length === 0) return '{}';
            
            const reviews = this.reviews.map(review => ({
                '@type': 'Review',
                'author': {
                    '@type': 'Person',
                    'name': review.name
                },
                'reviewRating': {
                    '@type': 'Rating',
                    'ratingValue': review.rating,
                    'bestRating': 5,
                    'worstRating': 1
                },
                'reviewBody': review.content,
                'datePublished': review.created_at
            }));
            
            const schema = {
                '@context': 'https://schema.org',
                '@type': 'SoftwareApplication',
                'name': document.querySelector('meta[name="app-name"]')?.content || 'InvoiceHub',
                'applicationCategory': 'BusinessApplication',
                'aggregateRating': {
                    '@type': 'AggregateRating',
                    'ratingValue': this.calculateAverageRating(),
                    'reviewCount': this.reviews.length,
                    'bestRating': 5,
                    'worstRating': 1
                },
                'review': reviews
            };
            
            return JSON.stringify(schema, null, 2);
        },
        
        calculateAverageRating() {
            if (this.reviews.length === 0) return 0;
            const sum = this.reviews.reduce((acc, review) => acc + review.rating, 0);
            return (sum / this.reviews.length).toFixed(1);
        }
    }
}
</script>
@endverbatim

