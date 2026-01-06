@props([
    'faqs' => [],
])

@php
// Default FAQs if none provided
$defaultFaqs = [
    [
        'question' => 'What is eTIMS?',
        'answer' => 'eTIMS is Kenya\'s new digital invoicing system by KRA. All invoices generated on our platform can be pushed directly to KRA via our built-in eTIMS integration.',
    ],
    [
        'question' => 'How do I accept mobile payments?',
        'answer' => 'We integrate with the Daraja 2.0 API. Simply enable Safaricom M-PESA in your settings, and your customers can pay directly from the invoice link.',
    ],
    [
        'question' => 'Is there a free plan or trial?',
        'answer' => 'Yes! You can start invoicing for free for 30 days, or use our basic free plan forever (no credit card needed).',
    ],
    [
        'question' => 'How do I pay for my subscription?',
        'answer' => 'We support M-Pesa for Kenyan users and Stripe (cards, banks, Apple Pay, Google Pay) for international users. Payment is processed securely through our payment partners.',
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
        'question' => 'How do I file taxes?',
        'answer' => 'Our reports are fully compliant with KRA requirements. You get real-time VAT statements that are ready for iTax filing.',
    ],
    [
        'question' => 'Is my data secure?',
        'answer' => 'Absolutely. We use bank-level encryption to protect your data and are fully compliant with the Kenya Data Protection Act.',
    ],
    [
        'question' => 'Can I use my own logo?',
        'answer' => 'Yes, you can upload your company logo and customize the invoice colors to match your brand identity perfectly.',
    ],
];

$faqsToShow = !empty($faqs) ? $faqs : $defaultFaqs;
@endphp

<div id="faq" class="bg-gray-50 dark:bg-[#1A1A1A] dark:bg-gray-900 dark:bg-[#0D0D0D] py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                Frequently Asked Questions
            </h2>
            <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                Everything you need to know about eTIMS, M-PESA integration, and billing.
            </p>
        </div>
        <dl class="mt-12 space-y-6 divide-y divide-gray-200 dark:divide-gray-700">
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
