<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'InvoiceHub') }} - KRA-Ready Invoicing for Kenyan SMEs</title>
  <meta name="description" content="Create invoices, accept Lipa na M-PESA, and file tax seamlessly with InvoiceHub – Kenya’s #1 eTIMS-compliant billing solution.">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Alpine.js (if not already included in app.js) -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Schema Markup -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "InvoiceHub",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "KES"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "500"
      }
    }, {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [{
        "@type": "Question",
        "name": "Is this KRA e-invoice (TIMS) compliant?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes. Every invoice you send includes the required QR code and tax information, and is automatically reported to the KRA e-TIMS system in real time."
        }
      }, {
        "@type": "Question",
        "name": "Can I accept M‑PESA payments?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Absolutely. Simply configure your Safaricom Daraja credentials and enable the Lipa na M‑PESA option. Your customers can pay invoices directly through M-PESA."
        }
      }, {
        "@type": "Question",
        "name": "How soon can I start invoicing?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Within minutes. Our guided setup gets you creating your first invoice in under 5 seconds."
        }
      }, {
        "@type": "Question",
        "name": "Is there a free plan or trial?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes. We offer a free trial so you can test all features before paying."
        }
      }]
    }
  </script>
</head>

<body class="font-sans antialiased text-gray-900">

  <x-landing.nav />

  <x-landing.hero />

  <x-landing.social-proof />

  <x-landing.features />

  <x-landing.faq />

  <x-landing.footer />

</body>

</html>