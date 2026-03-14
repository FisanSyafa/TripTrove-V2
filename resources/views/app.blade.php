<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'TripTrove Tour & Travel') }}</title>

        <!-- SEO Meta Tags -->
        <meta name="description" content="TripTrove Tour & Travel - Jelajahi destinasi impian Anda bersama kami. Paket wisata terbaik ke seluruh dunia dengan harga terjangkau. Explore the world with TripTrove. Jom melancong bersama TripTrove.">
        <meta name="keywords" content="travel, tour, wisata, paket wisata, liburan, vacation, holiday, trip, tour package, travel agency, agen travel, agen wisata, destinasi wisata, tempat wisata, wisata indonesia, wisata malaysia, wisata asia, wisata dunia, paket tour, paket liburan, jalan-jalan, traveling, backpacker, family trip, honeymoon, umroh, haji, tour guide, pemandu wisata, tiket pesawat, hotel, penginapan, akomodasi, transportasi, rental mobil, sewa mobil, adventure, petualangan, beach, pantai, mountain, gunung, culture, budaya, culinary, kuliner, shopping, belanja, city tour, island hopping, snorkeling, diving, hiking, trekking, camping, safari, cruise, kapal pesiar, domestic tour, international tour, group tour, private tour, custom tour, dream destination, destinasi impian, affordable travel, budget travel, luxury travel, family vacation, romantic getaway, adventure travel, eco tourism, sustainable tourism, cultural tourism, religious tourism, medical tourism, business travel, mice, meeting incentive, corporate travel, study tour, educational tour, school trip, graduation trip, company outing, team building, explore indonesia, jelajah nusantara, wonderful indonesia, visit malaysia, truly asia, amazing thailand, discover singapore, explore asia, world travel, global destinations, best travel deals, promo wisata, diskon travel, cheap flights, hotel murah, paket hemat, all inclusive, full board, half board, bed and breakfast, free cancellation, instant confirmation, online booking, book now, pesan sekarang, hubungi kami, contact us, whatsapp booking, customer service 24/7, trusted travel agent, verified partner, safe travel, aman terpercaya, berpengalaman, professional guide, licensed tour operator, registered travel agency, triptrove, trip trove, trove travel">
        <meta name="author" content="TripTrove Tour & Travel">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="googlebot" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="TripTrove Tour & Travel - Jelajahi Destinasi Impian Anda">
        <meta property="og:description" content="Paket wisata terbaik ke seluruh dunia dengan harga terjangkau. Explore the world with TripTrove. Jom melancong bersama TripTrove.">
        <meta property="og:image" content="{{ asset('images/logo_triptrove_bg.webp') }}">
        <meta property="og:site_name" content="TripTrove Tour & Travel">
        <meta property="og:locale" content="id_ID">
        <meta property="og:locale:alternate" content="en_US">
        <meta property="og:locale:alternate" content="ms_MY">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="TripTrove Tour & Travel - Jelajahi Destinasi Impian Anda">
        <meta name="twitter:description" content="Paket wisata terbaik ke seluruh dunia dengan harga terjangkau. Explore the world with TripTrove.">
        <meta name="twitter:image" content="{{ asset('images/logo_triptrove_bg.webp') }}">

        <!-- Favicon -->
        <link rel="icon" type="image/webp" href="{{ asset('images/logo_triptrove_bg.webp') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo_triptrove_bg.webp') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Schema.org Structured Data -->
        <script type="application/ld+json">
        @php
        echo json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            'name' => 'TripTrove Tour & Travel',
            'description' => 'Agen travel dan tour terpercaya dengan paket wisata terbaik ke seluruh dunia',
            'url' => config('app.url'),
            'logo' => asset('images/logo_triptrove_bg.webp'),
            'image' => asset('images/logo_triptrove_bg.webp'),
            'telephone' => '+' . config('app.admin_whatsapp_number'),
            'priceRange' => '$$',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'ID'
            ],
            'sameAs' => [
                config('app.url')
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.8',
                'reviewCount' => '500'
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Paket Wisata',
                'itemListElement' => [
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'TouristTrip',
                            'name' => 'Paket Wisata Domestik & Internasional'
                        ]
                    ]
                ]
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        @endphp
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
