@extends('layouts.app')

@section('title', 'Nde Official - Guitar Sessions & Brand Ambassador | Content Creator Indonesia')

@section('meta_description',
    'Nde Official - Exclusive Guitar Sessions by Alfarezi (Nde). Brand Ambassador untuk Crafter & E <div
        class="col-md-5 offset-md-1">
        <div class="block-content text-center">
            <h2 class="uppercase mb-4">About Nde - Alfarezi</h2>
            <p class="mb-4">
                I\'m <strong>Alfarezi</strong>, better known as <strong>Nde</strong> — a Gen Z content creator from
                Indonesia passionate about music and TikTok covers. With over <strong>9.7M video views</strong>, I create
                engaging guitar content and lifestyle videos that connect with today\'s youth.
            </p>
            <p class="mb-4">
                As a Brand Ambassador for <strong>Crafter Guitars</strong> 🇰🇷 and <strong>Enya Guitars</strong> 🇨🇳, I
                focus on delivering authentic, trend-driven content that brings value to both brands and my 1.2M+ engaged
                followers across social media platforms.
            </p>
            <a class="btn btn-primary with-ico" href="https://wa.me/+6281273796646"
                title="Contact Nde for business partnerships" aria-label="Contact Nde via WhatsApp for collaborations">
                <i class="icon-user"></i> Work with Me
            </a>
        </div>
    </div>Creator Gen Z Indonesia dengan 9.7M video views di TikTok.')

@section('meta_keywords', 'Nde Official, Alfarezi, guitar sessions, brand ambassador, content creator, TikTok covers, Crafter guitars, Enya guitars, Indonesia musician, guitar lessons, guitar class, kelas gitar, kursus gitar, nde, tiktok gitar, guitar tiktok, guiter covers, how to play guitar, belajar gitar, belajar gitar dari nol')

@section('og_title', 'Nde Official - Guitar Sessions & Brand Ambassador')
@section('og_description', 'Exclusive Guitar Sessions by Nde. Brand Ambassador untuk Crafter & Enya dengan 9.7M video
    views di TikTok. Join the class dan mulai perjalanan musikmu.')
@section('og_image', asset('compro/img/ndehero.JPEG'))
@section('og_url', url()->current())

@push('head')
    <!-- SEO Meta Tags -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="Alfarezi (Nde)">
    <meta name="revisit-after" content="7 days">
    <meta name="language" content="Indonesian">
    <meta name="geo.region" content="ID">
    <meta name="geo.country" content="Indonesia">
    <meta name="theme-color" content="#0F172A">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Nde Official">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">

    <!-- Structured Data for Person/Musician -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": ["Person", "MusicGroup"],
        "name": "Alfarezi",
        "alternateName": "Nde",
        "description": "Gen Z content creator from Indonesia passionate about music and TikTok covers",
        "url": "{{ url()->current() }}",
        "logo": "{{ asset('compro/img/ndelogo.webp') }}",
        "image": "{{ asset('compro/img/ndehero.webp') }}",
        "sameAs": [
            "https://www.instagram.com/rizqie.alfarezi/",
            "https://wa.me/+6281273796646"
        ],
        "jobTitle": "Content Creator & Brand Ambassador",
        "worksFor": [
            {
            "@@type": "Organization",
            "name": "Crafter Guitars"
            },
            {
            "@@type": "Organization",
            "name": "Enya Guitars"
            }
        ],
        "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": "+6281273796646",
            "contactType": "Business Inquiry",
            "email": "alfaareeziii.business@gmail.com"
        },
        "address": {
            "@@type": "PostalAddress",
            "addressCountry": "Indonesia"
        }
    }
    </script>

    <!-- Structured Data for Music Lessons -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Course",
        "name": "Exclusive Guitar Sessions by Nde",
        "description": "Learn guitar with a different approach. There's a difference between knowing how to play and knowing what to say with your sound.",
        "provider": {
            "@@type": "Person",
            "name": "Alfarezi (Nde)"
        },
        "courseMode": "Online",
        "teaches": "Guitar Playing",
        "inLanguage": "id",
        "url": "{{ route('registerclass') }}"
    }
    </script>

    <!-- Structured Data for Brand Partnerships -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebPage",
        "name": "Nde Official - Brand Partnerships",
        "description": "Brand Ambassador partnerships with major brands including Crafter, Enya, Nutrijel, Makarizo, Miniso, and more",
        "mainEntity": {
            "@@type": "Person",
            "name": "Alfarezi (Nde)",
            "hasOccupation": {
            "@@type": "Occupation",
            "name": "Brand Ambassador & Content Creator"
            }
        }
    }
    </script>

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Sitemap reference -->
    <link rel="sitemap" type="application/xml" href="{{ url('/sitemap.xml') }}">

    <!-- Hreflang for international SEO -->
    <link rel="alternate" hreflang="id" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}">

    <!-- Performance and loading hints -->
    <link rel="preload" href="{{ asset('compro/img/ndehero.webp') }}" as="image">
    <link rel="preload" href="{{ asset('compro/img/ndelogo.webp') }}" as="image">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
@endpush

@push('styles')
    <style>
        /* Full-bleed layout for /ndeofficial */
        .wrapper,
        .header {
            max-width: none !important;
            margin: 0 !important;
            width: 100% !important;
        }

        /* Make hero background truly edge-to-edge */
        .hero .slides,
        .hero .slides li {
            width: 100%;
        }

        .hero .slides li {
            height: 80vh !important;
            min-height: 60vh;
        }

        @media (min-width: 992px) {
            .hero .slides li {
                height: 100vh !important;
            }
        }

        .background-img {
            left: 0;
            right: 0;
            width: 100%;
        }
    </style>
@endpush

@section('content')

    <!-- Preloader -->
    <div class="loader">
        <div class="loader-inner">
            <svg width="120" height="220" viewBox="0 0 100 100" class="loading-spinner" version="1.1"
                xmlns="http://www.w3.org/2000/svg">
                <circle class="spinner" cx="50" cy="50" r="21" fill="#141414" stroke-width="2" />
            </svg>
        </div>
    </div>

    <!-- Header (local header used for the compro theme) -->
    <header class="header stopping">
        <div class="container">
            <div class="row">
                <div class="col-lg-2">
                    <a class="scroll logo" href="#wrapper">
                        <img class="mb-0" src="{{ asset('compro/img/ndelogo.webp') }}" alt="Nde Logo">
                    </a>
                </div>
                <div class="col-lg-10 text-right">
                    <nav class="main-nav">
                        <div class="toggle-mobile-but">
                            <a href="#" class="mobile-but">
                                <div class="lines"></div>
                            </a>
                        </div>
                        <ul class="main-menu list-inline" role="navigation" aria-label="Main navigation">
                            <li><a class="scroll list-inline-item" href="#wrapper" title="Nde Official Homepage">Home</a>
                            </li>
                            <li><a class="scroll list-inline-item" href="#about"
                                    title="About Nde - Guitar Instructor & Content Creator">About</a></li>
                            <li><a class="scroll list-inline-item" href="#discography"
                                    title="Brand Partnerships with Nde">Partnerships</a></li>
                            <li><a class="scroll list-inline-item" href="#registerclass"
                                    title="TikTok Analytics & Social Media Insights">Exposure</a></li>
                            <li><a class="scroll list-inline-item" href="#contact"
                                    title="Contact Nde for Guitar Lessons">Contact</a></li>
                            <li><a class="list-inline-item" href="{{ url('/registerclass') }}"
                                    title="Join Nde's Guitar Courses">Courses</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb Navigation (hidden but SEO-friendly) -->
    <nav aria-label="Breadcrumb" class="sr-only">
        <ol itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="{{ url('/') }}">
                    <span itemprop="name">Home</span>
                </a>
                <meta itemprop="position" content="1" />
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name">Nde Official</span>
                <meta itemprop="position" content="2" />
            </li>
        </ol>
    </nav>

    <!-- Page wrapper -->
    <div class="wrapper">
        <!-- Hero Section -->
        <section class="hero" role="banner">
            <div class="main-slider slider flexslider">
                <ul class="slides">
                    <li>
                        <div class="background-img overlay zoom">
                            <img src="{{ asset('compro/img/ndehero.webp') }}"
                                alt="Nde Official - Alfarezi playing guitar, Brand Ambassador Crafter and Enya guitars">
                        </div>
                        <div class="container hero-content">
                            <div class="row">
                                <div class="col-sm-12 text-center">
                                    <div class="inner-hero">
                                        <h1 class="large text-white mb-4">Nde Official
                                        </h1>
                                        <h2 class="uppercase h4">Exclusive Guitar Sessions by Nde</h2>
                                        <p class="text-white mt-3 mb-4">Learn guitar with a different approach from
                                            Indonesia's top content creator with 9.7M TikTok video views</p>
                                        <a class="btn btn-primary mt-4" href="#about"
                                            aria-label="Learn more about Nde Official guitar sessions">learn more</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li>
                        <div class="background-img overlay zoom">
                            <img src="{{ asset('compro/img/ndehero2.webp') }}"
                                alt="Nde Official Brand Ambassador - Open for partnerships with brands and companies">
                        </div>
                        <div class="container hero-content">
                            <div class="row">
                                <div class="col-sm-12 text-center">
                                    <div class="inner-hero">
                                        <h1 class="large text-white mb-4">Open for Brand Partnerships</h1>
                                        <h2 class="uppercase h4">Content Creator, Influencer, Brand Ambassador Indonesia
                                        </h2>
                                        <p class="text-white mt-3">Partner with an experienced brand ambassador with millions of views and high engagement.</p>
                                        <!-- <a class="video-play-but mt-4 popup-youtube" href="https://www.youtube.com/watch?v=Gc2en3nHxA4"></a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>


            <!-- About Section -->
            <!-- About Section -->
            <section id="about" class="about main brd-bottom" role="main">
                <img class="pattern-center" src="{{ asset('compro/img/right-pattern.webp') }}"
                    alt="Decorative pattern background">
                <div class="container">
                    <div class="row vertical-align">
                        <div class="col-md-5">
                            <div class="block-content text-center">
                                <h1 class="uppercase mb-0">A Different <br> WAY TO LEARN GUITAR</h1>
                                <p class="mt-2 lead w-95">"There's a difference between knowing how to play and knowing
                                    what to say with your sound."</p>
                                <img class="sing mb-0" src="{{ asset('compro/img/ttd-nde.webp') }}"
                                    alt="Signature of Nde - Alfarezi guitar instructor">
                            </div>
                        </div>
                        <div class="col-md-6 offset-md-1">
                            <div class="block-content">
                                <ul class="block-images row">
                                    <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde1.webp') }}"
                                            alt="Nde Alfarezi playing acoustic guitar - Guitar instructor and content creator"
                                            loading="lazy"></li>
                                    <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde2.webp') }}"
                                            alt="Nde Alfarezi with electric guitar - Brand ambassador for guitar brands"
                                            loading="lazy"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gap-one-bottom-md"></div>

                <div class="container">
                    <div class="row vertical-align">
                        <div class="col-md-6">
                            <div class="block-content">
                                <ul class="block-images row">
                                    <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde3.JPEG') }}"
                                            alt="Nde Alfarezi performing with acoustic guitar - Professional guitar instructor"
                                            loading="lazy"></li>
                                    <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde4.JPEG') }}"
                                            alt="Nde Alfarezi content creator setup - Behind the scenes of TikTok guitar content creation"
                                            loading="lazy"></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-5 offset-md-1">
                            <div class="block-content text-center">
                                <h1 class="uppercase mb-4">About Me</h1>
                                <p class="mb-4">
                                    I'm Alfarezi, better known as <strong>Nde</strong> — a Gen Z content creator from
                                    Indonesia passionate about music and TikTok covers. I also share lifestyle content that
                                    connects with today’s youth.
                                </p>
                                <p class="mb-4">
                                    As a Brand Ambassador for <strong>Crafter</strong> 🇰🇷 and <strong>Enya</strong> 🇨🇳,
                                    I focus on delivering engaging, trend-driven content that brings value to both brands
                                    and followers.
                                </p>
                                <a class="btn btn-primary with-ico" href="https://wa.me/+6281273796646">
                                    <i class="icon-user"></i> Work with Me
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Partnership Section -->
            <section id="discography" class="about main brd-bottom" role="region"
                aria-labelledby="partnerships-heading">
                <img class="pattern-center" src="{{ asset('compro/img/right-pattern.webp') }}"
                    alt="Decorative pattern background">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-9 mb-3.5">
                            <div class="block-content text-center gap-one-bottom-md">
                                <div class="block-title mb-5">
                                    <h3 class="uppercase mb-1">Brand Partnerships</h3>
                                    <h2 id="partnerships-heading" class="uppercase mb-0">Brand Ambassador Indonesia</h2>
                                    <p class="mt-3">Trusted by major brands across Indonesia and Asia for authentic
                                        content creation and brand representation</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Repeat this block for each partnership -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect" title="Kapal API - Brand Partnership with Nde">
                                    <div class="block-album p-3">
                                        <img src="{{ asset('compro/img/logopartnership/kapalapi.webp') }}"
                                            alt="Kapal API brand partnership logo - Coffee brand collaboration with Nde content creator">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 2 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect" title="Gatsby Hair Brand Partnership">
                                    <div class="block-album p-3">
                                        <img src="{{ asset('compro/img/logopartnership/getsby.webp') }}"
                                            alt="Gatsby hair brand logo - Brand partnership with Nde content creator">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 3 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect" title="Nutrijel Brand Collaboration">
                                    <div class="block-album p-3 text-center">
                                        <img src="{{ asset('compro/img/logopartnership/nutrijel.webp') }}"
                                            alt="Nutrijel brand logo - Food and beverage brand partnership with Nde Indonesia"
                                            class="img-fluid" style="max-height: 110px; object-fit: contain;">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 4 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect" title="Makarizo Hair Care Partnership">
                                    <div class="block-album p-3 text-center">
                                        <img src="{{ asset('compro/img/logopartnership/makarizo.webp') }}"
                                            alt="Makarizo hair care brand logo - Beauty brand collaboration with Nde content creator"
                                            class="img-fluid"
                                            style="max-height: 85px; object-fit: contain; margin-top: 20px;">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 5 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3 text-center">
                                        <img src="{{ asset('compro/img/logopartnership/miniso.webp') }}" alt="Miniso"
                                            class="img-fluid" style="max-height: 110px; object-fit: contain;">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 6 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3">
                                        <img src="{{ asset('compro/img/logopartnership/garnier.webp') }}" alt="Garnier">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 7 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3 text-center">
                                        <img src="{{ asset('compro/img/logopartnership/uniqlo.webp') }}" alt="uniqlo"
                                            class="img-fluid"
                                            style="max-height: 85px; object-fit: contain; margin-top: 20px;">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 8 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3">
                                        <img src="{{ asset('compro/img/logopartnership/maybellin.webp') }}" alt="maybeline">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 9 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3">
                                        <img src="{{ asset('compro/img/logopartnership/yupi.webp') }}" alt="yupi">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 10 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3">
                                        <img src="{{ asset('compro/img/logopartnership/pikopi.webp') }}" alt="pikopi">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 11 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3">
                                        <img src="{{ asset('compro/img/logopartnership/tomoro.webp') }}" alt="tomoro">
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 12 -->
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="block-content">
                                <a href="" class="hover-effect">
                                    <div class="block-album p-3 text-center">
                                        <img src="{{ asset('compro/img/logopartnership/tugujogja.webp') }}" alt="Tugujogja"
                                            class="img-fluid"
                                            style="max-height: 1000px; object-fit: contain; margin-bottom: 20px;">
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Add more partnerships as needed... -->
                </div>

                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8 col-md-10 mt-5 mb-5">
                        <div class="block-content gap-one-top-md text-center">
                            <h2 class="mb-0">A proud part of My journey</h2><br>
                            <h5 class="uppercase list-inline-item">I used to be one of the brand’s talents and ambassadors,
                                sharing its vision with pride.</h5>
                        </div>
                    </div>
                </div>
    </div>
    </section>

    <!-- Registerclass Section -->
    <section id="registerclass" class="custom-dashboard-section py-5" role="region" aria-labelledby="insights-heading">
        <div class="container">
            <h2 id="insights-heading" class="text-center mb-5 fw-bold">TikTok Audience Insights - Nde Official Statistics
            </h2>
            <p class="text-center mb-4">Real performance data showcasing Nde's reach and engagement as a content creator
                and brand ambassador</p>

            <div class="row text-center mb-5" itemscope itemtype="https://schema.org/SocialMediaPosting">
                <meta itemprop="author" content="Alfarezi (Nde)">
                <meta itemprop="datePublished" content="{{ date('Y-m-d') }}">

                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded" itemprop="interactionStatistic" itemscope
                        itemtype="https://schema.org/InteractionCounter">
                        <meta itemprop="interactionType" content="https://schema.org/WatchAction">
                        <meta itemprop="userInteractionCount" content="9700000">
                        <h5 class="text-muted">Video Views</h5>
                        <h3 class="fw-bold">9.7M</h3>
                        <p style="color: #00FF77;">+4.2M (78.3%)</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded" itemprop="interactionStatistic" itemscope
                        itemtype="https://schema.org/InteractionCounter">
                        <meta itemprop="interactionType" content="https://schema.org/ViewAction">
                        <meta itemprop="userInteractionCount" content="86000">
                        <h5 class="text-muted">Profile Views</h5>
                        <h3 class="fw-bold">86K</h3>
                        <p style="color: #00FF77;">+46.5K (116.1%)</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded" itemprop="interactionStatistic" itemscope
                        itemtype="https://schema.org/InteractionCounter">
                        <meta itemprop="interactionType" content="https://schema.org/LikeAction">
                        <meta itemprop="userInteractionCount" content="1200000">
                        <h5 class="text-muted">Likes</h5>
                        <h3 class="fw-bold">1.2M</h3>
                        <p style="color: #00FF77;">+710K (148.1%)</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded" itemprop="interactionStatistic" itemscope
                        itemtype="https://schema.org/InteractionCounter">
                        <meta itemprop="interactionType" content="https://schema.org/CommentAction">
                        <meta itemprop="userInteractionCount" content="98000">
                        <h5 class="text-muted">Comments</h5>
                        <h3 class="fw-bold">98K</h3>
                        <p style="color: #00FF77;">+40K (69.3%)</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="custom-card shadow p-4 rounded">
                        <h5 class="mb-3">Gender Breakdown</h5>
                        <canvas id="genderChart" class="dashboard-chart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="custom-card shadow p-4 rounded">
                        <h5 class="mb-3">Top Locations</h5>
                        <canvas id="locationChart" class="dashboard-chart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Promo video player (Bunny CDN) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="custom-card shadow p-4 rounded">
                        @if (!empty($promo_title))
                            <h5 class="mb-3 text-center" style="color: #ddd; font-weight:600;">{{ $promo_title }}</h5>
                        @endif
                        <div id="promo-player-container"
                            style="position:relative;padding-top:56.25%;background:#000;border-radius:6px;overflow:hidden;">
                            @php
                                $promoGuid = $promo_bunny_guid ?? null;
                                $promoThumb = null;
                                if ($promoGuid) {
                                    try {
                                        $meta = \App\Http\Controllers\BunnyController::getVideoStatus($promoGuid);
                                        // Bunny metadata may include 'thumbnailFileName' or 'thumbnail' fields
                                        $thumbFile = null;
                                        if (!empty($meta['thumbnailFileName'])) {
                                            $thumbFile = $meta['thumbnailFileName'];
                                        } elseif (!empty($meta['thumbnail'])) {
                                            $thumbFile = $meta['thumbnail'];
                                        }
                                        // fallback to 'thumbnail.jpg'
                                        if (!$thumbFile) {
                                            $thumbFile = 'thumbnail.jpg';
                                        }
                                        $promoThumb = \App\Http\Controllers\BunnyController::signThumbnailUrl(
                                            $promoGuid,
                                            300,
                                            $thumbFile,
                                        );
                                    } catch (\Throwable $e) {
                                        $promoThumb =
                                            \App\Http\Controllers\BunnyController::cdnUrl($promoGuid) .
                                            '/thumbnail.jpg';
                                    }
                                }
                            @endphp
                            <div id="promo-video-placeholder"
                                style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;background-size:cover;background-position:center;{{ $promoThumb ? "background-image:url('$promoThumb');" : '' }}">
                                <button id="promo-play-button" class="btn btn-primary" aria-label="Play promo"
                                    style="width:64px;height:64px;border-radius:999px;display:flex;align-items:center;justify-content:center;padding:0;border:none;background:rgba(255,255,255,0.95);">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M8 5v14l11-7L8 5z" fill="#000" />
                                    </svg>
                                </button>
                            </div>
                            <!-- video element will be injected here -->
                        </div>
                    </div>
                    <div class="text-center" style="margin-top:16px">
                        <a href="{{ route('registerclass') }}" class="btn btn-primary with-ico"
                            style="padding:10px 18px;border-radius:24px;" title="Daftar kelas guitar dengan Nde Official"
                            aria-label="Register for Nde's guitar classes">
                            <i class="icon-user"></i> Join Guitar Class
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </div>

        </div>
    </section>
    <!-- Contact Section -->
    <section id="contact" class="contact main top bg-secondary text-white py-5" role="region"
        aria-labelledby="contact-heading">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-9 text-center">
                    <div class="block-title">
                        <div class="text-center">
                            <img src="{{ asset('compro/img/ndelogo.webp') }}"
                                alt="Nde Official Logo - Contact for guitar lessons and brand partnerships"
                                style="width: 45px;">
                        </div>
                        <h3 class="uppercase mb-2">Connect With Nde</h3>
                        <h2 id="contact-heading" class="uppercase mb-0">Join The Guitar Class</h2>
                    </div>
                    <p class="mt-3">Join the exclusive guitar class and start your musical journey with Indonesia's top
                        content creator. Contact Nde through any platform below for guitar lessons or business partnerships.
                    </p>
                </div>
            </div>

            <div class="row justify-content-center mt-5">
                <div class="col-12 col-lg-10">
                    <ul class="feature-list feature-list-sm text-center row gap-one-bottom-sm">
                        <li class="col-sm-4 col-lg-4">
                            <div class="card block-info text-center">
                                <div class="card-body pt-0">
                                    <h3 class="uppercase h2">WhatsApp</h3>
                                    <p class="mb-0">
                                        <em class="h5 mb-1 uppercase swap-color">Contact Nde Directly</em><br>
                                        <a href="https://wa.me/+6281273796646" target="_blank" class="text-white"
                                            title="Contact Nde via WhatsApp for guitar lessons and partnerships"
                                            aria-label="WhatsApp contact for Nde guitar instructor">
                                            +62 812-7379-6646
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="col-sm-4 col-lg-4">
                            <div class="card block-info text-center">
                                <div class="card-body pt-0">
                                    <h3 class="uppercase h2">Email</h3>
                                    <p class="mb-0">
                                        <em class="h5 mb-1 uppercase swap-color">Business Inquiry</em><br>
                                        <a href="mailto:alfaareeziii.business@gmail.com" class="text-white"
                                            title="Email Nde for business partnerships and brand collaborations"
                                            aria-label="Email contact for business inquiries with Nde">
                                            alfaareeziii.business@gmail.com
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="col-sm-4 col-lg-4">
                            <div class="card block-info text-center">
                                <div class="card-body pt-0">
                                    <h3 class="uppercase h2">Instagram</h3>
                                    <p class="mb-0">
                                        <em class="h5 mb-1 uppercase swap-color">Follow Nde Official</em><br>
                                        <a href="https://www.instagram.com/rizqie.alfarezi/" target="_blank"
                                            class="text-white"
                                            title="Follow Nde on Instagram for guitar content and lifestyle updates"
                                            aria-label="Nde Official Instagram profile">
                                            @rizqie.alfarezi
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <footer class="footer pb-5 bg-secondary text-center text-white" role="contentinfo">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-md-12">
                    <div class="block-content pt-4 brd-top">
                        <!-- Footer navigation -->
                        <nav class="footer-nav mb-4" aria-label="Footer navigation">
                            <ul class="list-inline mb-3">
                                <li class="list-inline-item"><a href="#about" class="text-white"
                                        title="About Nde">About</a></li>
                                <li class="list-inline-item">|</li>
                                <li class="list-inline-item"><a href="#discography" class="text-white"
                                        title="Brand Partnerships">Partnerships</a></li>
                                <li class="list-inline-item">|</li>
                                <li class="list-inline-item"><a href="{{ url('/registerclass') }}" class="text-white"
                                        title="Guitar Courses">Courses</a></li>
                                <li class="list-inline-item">|</li>
                                <li class="list-inline-item"><a href="#contact" class="text-white"
                                        title="Contact Nde">Contact</a></li>
                            </ul>
                        </nav>

                        <!-- Social media links -->
                        {{-- <div class="social-links mb-3">
                            <a href="https://www.instagram.com/rizqie.alfarezi/" target="_blank" class="text-white me-3"
                                title="Follow Nde on Instagram" aria-label="Nde's Instagram profile">Instagram</a>
                            <a href="https://wa.me/+6281273796646" target="_blank" class="text-white"
                                title="Contact Nde via WhatsApp" aria-label="WhatsApp contact">WhatsApp</a>
                        </div> --}}

                        <!-- Copyright and description -->
                        <p class="mb-2"><strong>Nde Official</strong> - Guitar Sessions & Brand Ambassador Indonesia</p>
                        <p class="mb-0 mt-3">&copy; 2025 Nde Official (Alfarezi). All rights reserved — Powered by
                            <em>WardellTech</em></p>
                        <p class="small mt-2 text-muted">Professional guitar instructor, content creator, and brand
                            ambassador with 9.7M+ video views</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <a class="block-top scroll hover-effect" href="#wrapper"><i class="icon-angle-up"></i></a>
    </div>

@endsection

@push('scripts')
    <!-- Scripts -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const genderChart = new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: ['Female', 'Male', 'Other'],
                datasets: [{
                    data: [50, 49, 1],
                    backgroundColor: ['#0F172A', '#BE185D', '#6B21A8'],
                    borderColor: '#1a1a1a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#fff'
                        }
                    }
                }
            }
        });

        const locationChart = new Chart(document.getElementById('locationChart'), {
            type: 'bar',
            data: {
                labels: ['Indonesia', 'Philippines', 'Malaysia', 'Others'],
                datasets: [{
                    label: 'Audience (%)',
                    data: [79.2, 9.5, 7.4, 1.4],
                    backgroundColor: ['#1E3A8A', '#9D174D', '#7C3AED', '#F59E0B'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            color: '#fff'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#fff'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#fff'
                        }
                    }
                }
            }
        });

        let lastScrollTop = 0;
        const navbar = document.querySelector('.header');

        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop) {
                // Scroll ke bawah → sembunyikan
                navbar.style.top = "-80px"; // tinggi navbar
            } else {
                // Scroll ke atas → tampilkan
                navbar.style.top = "0";
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // biar nggak negatif
        });
        document.addEventListener("DOMContentLoaded", () => {
            const mobileBut = document.querySelector(".mobile-but");
            const mainMenu = document.querySelector(".main-menu");

            if (!mobileBut || !mainMenu) return;

            // Toggle menu saat hamburger diklik (hanya di mobile)
            mobileBut.addEventListener("click", (e) => {
                e.preventDefault();
                mainMenu.classList.toggle("active");
            });

            // Auto close menu saat klik item menu di mobile
            mainMenu.querySelectorAll("a").forEach((link) => {
                link.addEventListener("click", () => {
                    if (window.innerWidth <= 990) {
                        mainMenu.classList.remove("active");
                    }
                });
            });
        });
    </script>

    <script>
        // Chart initialisation (keeps responsive behaviour)
        (function() {
            try {
                const genderCtx = document.getElementById('genderChart');
                if (genderCtx) {
                    new Chart(genderCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Female', 'Male', 'Other'],
                            datasets: [{
                                data: [50, 49, 1],
                                backgroundColor: ['#0F172A', '#BE185D', '#6B21A8'],
                                borderColor: '#1a1a1a',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: {
                                        color: '#fff'
                                    }
                                }
                            }
                        }
                    });
                }

                const locCtx = document.getElementById('locationChart');
                if (locCtx) {
                    new Chart(locCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Indonesia', 'Philippines', 'Malaysia', 'Others'],
                            datasets: [{
                                label: 'Audience (%)',
                                data: [79.2, 9.5, 7.4, 1.4],
                                backgroundColor: ['#1E3A8A', '#9D174D', '#7C3AED', '#F59E0B'],
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: {
                                        color: '#fff'
                                    },
                                    grid: {
                                        color: 'rgba(255,255,255,0.1)'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        color: '#fff'
                                    },
                                    grid: {
                                        color: 'rgba(255,255,255,0.1)'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: '#fff'
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.warn('Chart init failed', e);
            }
        })();
    </script>

    <script>
        // Header hide-on-scroll and mobile menu toggle (kept compact)
        (function() {
            let lastScrollTop = 0;
            const navbar = document.querySelector('.header');
            if (navbar) {
                window.addEventListener('scroll', function() {
                    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    navbar.style.top = scrollTop > lastScrollTop ? '-80px' : '0';
                    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                const mobileBut = document.querySelector('.mobile-but');
                const mainMenu = document.querySelector('.main-menu');
                if (!mobileBut || !mainMenu) return;
                mobileBut.addEventListener('click', (e) => {
                    e.preventDefault();
                    mainMenu.classList.toggle('active');
                });
                mainMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
                    if (window.innerWidth <= 990) mainMenu.classList.remove('active');
                }));
            });
        })();
    </script>

    <script>
        // Promo player logic: fetch signed URL and play using HLS or native
        (function() {
            const playBtn = document.getElementById('promo-play-button');
            const placeholder = document.getElementById('promo-video-placeholder');
            const container = document.getElementById('promo-player-container');

            async function fetchPromoUrl() {
                try {
                    const res = await fetch('/promo-stream');
                    const j = await res.json();
                    return j.url || null;
                } catch (e) {
                    console.warn('Failed to fetch promo stream', e);
                    return null;
                }
            }

            function createAndPlay(url) {
                if (!url) return;
                const existing = document.getElementById('promo-html5-player');
                if (existing) {
                    try {
                        existing.pause();
                    } catch (e) {}
                    existing.remove();
                }
                const v = document.createElement('video');
                v.id = 'promo-html5-player';
                v.controls = true;
                v.setAttribute('playsinline', '');
                v.style.position = 'absolute';
                v.style.top = '0';
                v.style.left = '0';
                v.style.width = '100%';
                v.style.height = '100%';
                v.style.zIndex = '2';
                container.appendChild(v);
                if (placeholder) placeholder.style.display = 'none';

                const attach = () => {
                    if (window.Hls && Hls.isSupported()) {
                        const hls = new Hls();
                        hls.loadSource(url);
                        hls.attachMedia(v);
                        hls.on(Hls.Events.MANIFEST_PARSED, function() {
                            v.play().catch(() => {});
                        });
                    } else {
                        v.src = url;
                        v.addEventListener('loadedmetadata', () => v.play().catch(() => {}));
                    }
                };

                if (!window.Hls) {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
                    s.async = true;
                    s.onload = attach;
                    s.onerror = attach;
                    document.head.appendChild(s);
                } else attach();
            }

            if (playBtn) {
                playBtn.addEventListener('click', async function() {
                    playBtn.disabled = true;
                    playBtn.textContent = 'Loading...';
                    const url = await fetchPromoUrl();
                    if (!url) {
                        playBtn.textContent = 'Unavailable';
                        return;
                    }
                    createAndPlay(url);
                });
            }
        })();
    </script>
@endpush
