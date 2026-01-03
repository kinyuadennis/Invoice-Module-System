@props([
    'faqs' => [],
])

@php
// Default FAQs if none provided
$defaultFaqs = [
    [
        'question' => 'How do I pay for my subscription?',
        'answer' => 'We support M-Pesa for Kenyan users and Stripe (cards, banks) for international users. Payment is processed securely through our payment partners.',
    ],
    [
        'question' => 'What is the cancellation policy?',
        'answer' => 'You can cancel your subscription at any time. Your access continues until the end of your current billing period. No refunds for partial periods.',
    ],
    [
        'question' => 'Is VAT included in the price?',
        'answer' => 'Prices are displayed exclusive of VAT. VAT (16% in Kenya) will be added at checkout where applicable.',
    ],
    [
        'question' => 'Can I switch plans later?',
        'answer' => 'Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately, with prorated billing.',
    ],
    [
        'question' => 'Do you offer refunds?',
        'answer' => 'We offer a 14-day money-back guarantee for new subscriptions. Contact support within 14 days for a full refund.',
    ],
    [
        'question' => 'How does auto-renewal work?',
        'answer' => 'Subscriptions automatically renew at the end of each billing period. You can disable auto-renewal in your account settings at any time.',
    ],
];

$faqsToShow = !empty($faqs) ? $faqs : $defaultFaqs;
@endphp

<div id="faq" class="bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:py-16 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-center text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                Frequently asked questions
            </h2>
            <dl class="mt-6 space-y-6 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($faqsToShow as $index => $faq)
                    <div x-data="{ open: false }" class="pt-6">
                        <dt class="text-lg">
                            <button 
                                @click="open = !open" 
                                type="button" 
                                class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2B6EF6] dark:focus:ring-offset-gray-900" 
                                :aria-expanded="open"
                                :aria-controls="'faq-{{ $index }}'"
                            >
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $faq['question'] }}
                                </span>
                                <span class="ml-6 h-7 flex items-center">
                                    <svg 
                                        class="h-6 w-6 transform transition-transform duration-200" 
                                        :class="{'rotate-180': open, 'rotate-0': !open}" 
                                        fill="none" 
                                        viewBox="0 0 24 24" 
                                        stroke="currentColor" 
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </button>
                        </dt>
                        <dd 
                            x-show="open" 
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="mt-2 pr-12" 
                            :id="'faq-{{ $index }}'"
                        >
                            <p class="text-base text-gray-500 dark:text-gray-400">
                                {{ $faq['answer'] }}
                            </p>
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</div>

