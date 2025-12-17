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
        }
        </script>
    </head>
    <body class="font-sans antialiased text-gray-900">
        
        <x-landing.nav />
        
        <x-landing.hero />
        
        <x-landing.social-proof />
        
        <x-landing.features />
        
        <x-landing.demo />
        
        <x-landing.faq />
        
        <x-landing.footer />

    </body>
</html>
