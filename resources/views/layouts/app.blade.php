<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Nde Official')</title>
    <meta name="description" content="@yield('description', 'Nde Official - Exclusive Guitar Sessions')">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Css -->
    <link href="{{ asset('compro/css/bootstrap.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/base.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/main.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/flexslider.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/magnific-popup.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/fonts.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="//fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('compro/img/ndelogo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    @stack('styles')
</head>

<body>
    @yield('content')

    <!-- Scripts -->
     
    <script src="{{ asset('compro/js/jquery-1.12.4.min.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.flexslider-min.js') }}"></script>
    <script src="{{ asset('compro/js/smooth-scroll.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('compro/js/twitterFetcher_min.js') }}"></script>
    <script src="{{ asset('compro/js/instafeed.min.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('compro/js/placeholders.min.js') }}"></script>
    <script src="{{ asset('compro/js/script.js') }}"></script>

    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')
</body>

</html>
