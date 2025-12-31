@extends('layouts.public')

@section('title', 'InvoiceHub - The Invoicing Platform Built for Kenyan Business')

@section('content')

<x-landing.hero-section />

<x-landing.feature-list />


<x-landing.invoicing-workflow />


{{-- Pricing section will go here --}}


<x-landing.faq-section />

<x-landing.footer />

    <x-demo-walkthrough />

@push('scripts')
@vite('resources/js/demo-landing.js')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [{
            "@type": "Question",
            "name": "What is eTIMS?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "eTIMS is Kenya’s new digital invoicing system by KRA. All invoices generated on our platform can be pushed directly to KRA via our built-in eTIMS integration."
            }
        }, {
                "@type": "Question",
            "name": "How do I accept mobile payments?",
                "acceptedAnswer": {
                    "@type": "Answer",
                "text": "We integrate with the Daraja 2.0 API. Simply enable Safaricom M-PESA in your settings, and your customers can pay directly from the invoice link."
            }
        }, {
            "@type": "Question",
            "name": "Is there a free plan or trial?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes! You can start invoicing for free for 30 days, or use our basic free plan forever (no credit card needed)."
            }
        }, {
            "@type": "Question",
            "name": "How do I file taxes?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Our reports are fully compliant with KRA requirements. You get real-time VAT statements that are ready for iTax filing."
            }
        }, {
            "@type": "Question",
            "name": "Is my data secure?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Absolutely. We use bank-level encryption to protect your data and are fully compliant with the Kenya Data Protection Act."
            }
        }, {
            "@type": "Question",
            "name": "Can I use my own logo?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, you can upload your company logo and customize the invoice colors to match your brand identity perfectly."
            }
        }]
    }
    </script>
@endpush

@endsection