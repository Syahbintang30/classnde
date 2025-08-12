@extends('layouts.app')

@section('title', 'Nde Official')

@section('content')
<body>
    <!-- Preloader -->
    <div class="loader">
        <div class="loader-inner">
            <svg width="120" height="220" viewBox="0 0 100 100" class="loading-spinner" version="1.1"
                xmlns="http://www.w3.org/2000/svg">
                <circle class="spinner" cx="50" cy="50" r="21" fill="#141414" stroke-width="2" />
            </svg>
        </div>
    </div>

    <!-- Header -->
    <header class="header stopping">
        <div class="container">
            <div class="row">
                <div class="col-lg-2">
                    <a class="scroll logo" href="#wrapper">
                        <img class="mb-0" src="{{ asset('compro/img/ndelogo.png') }}" alt="Nde Logo">
                    </a>
                </div>
                <div class="col-lg-10 text-right">
                    <nav class="main-nav">
                        <div class="toggle-mobile-but">
                            <a href="javascript:void(0)" class="mobile-but">
                                <div class="lines"></div>
                            </a>
                        </div>
                        <ul class="main-menu list-inline">
                            <li><a class="scroll list-inline-item" href="#wrapper">Home</a></li>
                            <li><a class="scroll list-inline-item" href="#about">About</a></li>
                            <li><a class="scroll list-inline-item" href="#discography">Partnerships</a></li>
                            <li><a class="scroll list-inline-item" href="#dashboard">Exposure</a></li>
                            <li><a class="scroll list-inline-item" href="#contact">Contact</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <!-- isi konten di sini -->
    </section>
</body>



<!-- Wrapper -->
<div class="wrapper">
    <!-- Hero Section -->
    <section class="hero">
        <div class="main-slider slider flexslider">
            <ul class="slides">
                <li>
                    <div class="background-img overlay zoom">
                        <img src="{{ asset('compro/img/ndehero.JPEG') }}" alt="Nde Hero 1">
                    </div>
                    <div class="container hero-content">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <div class="inner-hero">
                                    <h1 class="large text-white mb-4">Nde Official</h1>
                                    <h4 class="uppercase h4">Exclusive Guitar Sessions by Nde</h4>
                                    <a class="btn btn-primary mt-4" href="#">learn more</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <li>
                    <div class="background-img overlay zoom">
                        <img src="{{ asset('compro/img/ndehero2.jpg') }}" alt="Nde Hero 2">
                    </div>
                    <div class="container hero-content">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <div class="inner-hero">
                                    <h1 class="large text-white mb-4">Open for Partnerships</h1>
                                    <h4 class="uppercase h4">Content Creator, Influencer, Brand Ambassador</h4>
                                    <!-- <a class="video-play-but mt-4 popup-youtube" href="https://www.youtube.com/watch?v=Gc2en3nHxA4"></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>


    <!-- About Section -->
    <section id="about" class="about main brd-bottom">
        <img class="pattern-center" src="{{ asset('compro/img/right-pattern.png') }}" alt="Pattern">
        <div class="container">
            <div class="row vertical-align">
                <div class="col-md-5">
                    <div class="block-content text-center">
                        <h1 class="uppercase mb-0">A Different <br> WAY TO LEARN GUITAR</h1>
                        <p class="mt-2 lead w-95">"There’s a difference between knowing how to play and knowing what to say with your sound."</p>
                        <img class="sing mb-0" src="{{ asset('compro/img/ttd-nde.png') }}" alt="Signature Nde">
                    </div>
                </div>
                <div class="col-md-6 offset-md-1">
                    <div class="block-content">
                        <ul class="block-images row">
                            <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde1.JPEG') }}" alt="Nde 1"></li>
                            <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde2.JPEG') }}" alt="Nde 2"></li>
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
                            <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde3.JPEG') }}" alt="Nde 3"></li>
                            <li class="col-md-6 col-sm-6"><img src="{{ asset('compro/img/nde4.JPEG') }}" alt="Nde 4"></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-5 offset-md-1">
                    <div class="block-content text-center">
                        <h1 class="uppercase mb-4">About Me</h1>
                        <p class="mb-4">
                            I'm Alfarezi, better known as <strong>Nde</strong> — a Gen Z content creator from Indonesia passionate about music and TikTok covers. I also share lifestyle content that connects with today’s youth.
                        </p>
                        <p class="mb-4">
                            As a Brand Ambassador for <strong>Crafter</strong> 🇰🇷 and <strong>Enya</strong> 🇨🇳, I focus on delivering engaging, trend-driven content that brings value to both brands and followers.
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
    <section id="discography" class="about main brd-bottom">
        <img class="pattern-center" src="{{ asset('compro/img/right-pattern.png') }}" alt="Pattern">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-9 mb-3.5">
                    <div class="block-content text-center gap-one-bottom-md">
                        <div class="block-title mb-5">
                            <h3 class="uppercase mb-1">Talents</h3>
                            <h1 class="uppercase mb-0">Brand ambassador</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
    <!-- Repeat this block for each partnership -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3">
                    <img src="{{ asset('compro/img/logopartnership/1.png') }}" alt="kapalapi">
                </div>
            </a>
        </div>
    </div>

    <!-- 2 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3">
                    <img src="{{ asset('compro/img/logopartnership/2.png') }}" alt="Gatsby">
                </div>
            </a>
        </div>
    </div>

    <!-- 3 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3 text-center">
                    <img src="{{ asset('compro/img/logopartnership/nutrijel.png') }}" alt="nutrijel" class="img-fluid" style="max-height: 110px; object-fit: contain;">
                </div>
            </a>
        </div>
    </div>

    <!-- 4 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3 text-center">
                    <img src="{{ asset('compro/img/logopartnership/4.png') }}" alt="Makarizo" class="img-fluid" style="max-height: 85px; object-fit: contain; margin-top: 20px;">
                </div>
            </a>
        </div>
    </div>

    <!-- 5 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3 text-center">
                    <img src="{{ asset('compro/img/logopartnership/miniso.png') }}" alt="Miniso" class="img-fluid" style="max-height: 110px; object-fit: contain;">
                </div>
            </a>
        </div>
    </div>

    <!-- 6 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3">
                    <img src="{{ asset('compro/img/logopartnership/6.png') }}" alt="Garnier">
                </div>
            </a>
        </div>
    </div>

    <!-- 7 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3 text-center">
                    <img src="{{ asset('compro/img/logopartnership/uniqlo.png') }}" alt="uniqlo" class="img-fluid" style="max-height: 85px; object-fit: contain; margin-top: 20px;">
                </div>
            </a>
        </div>
    </div>

    <!-- 8 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3">
                    <img src="{{ asset('compro/img/logopartnership/8.png') }}" alt="maybeline">
                </div>
            </a>
        </div>
    </div>

    <!-- 9 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3">
                    <img src="{{ asset('compro/img/logopartnership/9.png') }}" alt="yupi">
                </div>
            </a>
        </div>
    </div>

    <!-- 10 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3">
                    <img src="{{ asset('compro/img/logopartnership/10.png') }}" alt="pikopi">
                </div>
            </a>
        </div>
    </div>

    <!-- 11 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3">
                    <img src="{{ asset('compro/img/logopartnership/11.png') }}" alt="tomoro">
                </div>
            </a>
        </div>
    </div>

    <!-- 12 -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="block-content">
            <a href="" class="hover-effect">
                <div class="block-album p-3 text-center">
                    <img src="{{ asset('compro/img/logopartnership/12.png') }}" alt="Tugujogja" class="img-fluid" style="max-height: 1000px; object-fit: contain; margin-bottom: 20px;">
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
                        <h5 class="uppercase list-inline-item">I used to be one of the brand’s talents and ambassadors, sharing its vision with pride.</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Section -->
    <section id="dashboard" class="custom-dashboard-section py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Tiktok Audience Insights</h2>

            <div class="row text-center mb-5">
                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded">
                        <h5 class="text-muted">Video Views</h5>
                        <h3 class="fw-bold">9.7M</h3>
                        <p style="color: #00FF77;">+4.2M (78.3%)</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded">
                        <h5 class="text-muted">Profile Views</h5>
                        <h3 class="fw-bold">86K</h3>
                        <p style="color: #00FF77;">+46.5K (116.1%)</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded">
                        <h5 class="text-muted">Likes</h5>
                        <h3 class="fw-bold">1.2M</h3>
                        <p style="color: #00FF77;">+710K (148.1%)</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="custom-card shadow p-4 rounded">
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
        </div>
    </section>
    <!-- Contact Section -->
    <section id="contact" class="contact main top bg-secondary text-white py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-9 text-center">
                    <div class="block-title">
                        <div class="text-center">
                            <img src="{{ asset('compro/img/ndelogo.png') }}" alt="NDE Logo" style="width: 45px;">
                        </div>
                        <h6 class="uppercase mb-2">Connect With Me</h6>
                        <h1 class="uppercase mb-0">Join The Class</h1>
                    </div>
                    <p class="mt-3">Join the class and start your musical journey. Contact me through any platform below.</p>
                </div>
            </div>

            <div class="row justify-content-center mt-5">
                <div class="col-12 col-lg-10">
                    <ul class="feature-list feature-list-sm text-center row gap-one-bottom-sm">
                        <li class="col-sm-4 col-lg-4">
                            <div class="card block-info text-center">
                                <div class="card-body pt-0">
                                    <h2 class="uppercase h2">WhatsApp</h2>
                                    <p class="mb-0">
                                        <em class="h5 mb-1 uppercase swap-color">Contact Me</em><br>
                                        <a href="https://wa.me/+6281273796646" target="_blank" class="text-white">
                                            +62 812-7379-6646
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="col-sm-4 col-lg-4">
                            <div class="card block-info text-center">
                                <div class="card-body pt-0">
                                    <h2 class="uppercase h2">Email</h2>
                                    <p class="mb-0">
                                        <em class="h5 mb-1 uppercase swap-color">Business Inquiry</em><br>
                                        <a href="mailto:alfaareeziii.business@gmail.com" class="text-white">
                                            alfaareeziii.business@gmail.com
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="col-sm-4 col-lg-4">
                            <div class="card block-info text-center">
                                <div class="card-body pt-0">
                                    <h2 class="uppercase h2">Instagram</h2>
                                    <p class="mb-0">
                                        <em class="h5 mb-1 uppercase swap-color">My Instagram</em><br>
                                        <a href="https://www.instagram.com/rizqie.alfarezi/" target="_blank" class="text-white">
                                            instagram.com/rizqie.alfarezi
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
        <footer class="footer pb-5 bg-secondary text-center text-white">
            <div class="container">
                <div class="row justify-content-center align-items-center">
                    <div class="col-md-12">
                        <div class="block-content pt-4 brd-top">
                            <p class="mb-0 mt-3">&copy; 2025 All rights reserved — Powered by <em>WardellTech</em></p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <a class="block-top scroll hover-effect" href="#wrapper"><i class="icon-angle-up"></i></a>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('compro/js/jquery-1.12.4.min.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.flexslider-min.js') }}"></script>
    <script src="{{ asset('compro/js/smooth-scroll.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('compro/js/audio.min.js') }}"></script>
    <script src="{{ asset('compro/js/twitterFetcher_min.js') }}"></script>
    <script src="{{ asset('compro/js/instafeed.min.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('compro/js/placeholders.min.js') }}"></script>
    <script src="{{ asset('compro/js/script.js') }}"></script>

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

    @stack('scripts')
</body>

@endsection
