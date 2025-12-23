<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('') }}">
    <meta name="theme-color" content="#2196F3">
    <meta name="default-locale" content="{{ config('app.locale') }}">

    <title>@yield('title') - {{ config('app.name') }}</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('favicons/site.webmanifest') }}">

    <!-- App styles -->
    <link rel="stylesheet" href="{{ url('css/backend.css') }}?v={{ filemtime(base_path('public/css/backend.css')) }}">
    @yield('styles')
</head>

<body class="" data-ma-theme="green">
<main class="main">

    @include('layouts.header')

    <section class="content content--full">
        <header id="app-header" class="content__title">
            <div class="row">
                <div class="col-md-6">
                    <h1>@yield('title')</h1>
                </div>
                <div class="col-md-6 text-right">
                    @yield('page_actions')
                </div>
            </div>
        </header>

        <main id="app-content">
            @yield('content')
        </main>

    </section>
</main>

<!-- Older IE warning message -->
<!--[if IE]>
<div class="ie-warning">
    <h1>Warning!!</h1>
    <p>You are using an outdated version of Internet Explorer, please upgrade to any of the following web browsers to
        access this website.</p>

    <div class="ie-warning__downloads">
        <a href="http://www.google.com/chrome">
            <img src="img/browsers/chrome.png" alt="">
        </a>

        <a href="https://www.mozilla.org/en-US/firefox/new">
            <img src="img/browsers/firefox.png" alt="">
        </a>

        <a href="http://www.opera.com">
            <img src="img/browsers/opera.png" alt="">
        </a>

        <a href="https://support.apple.com/downloads/safari">
            <img src="img/browsers/safari.png" alt="">
        </a>

        <a href="https://www.microsoft.com/en-us/windows/microsoft-edge">
            <img src="img/browsers/edge.png" alt="">
        </a>

        <a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">
            <img src="img/browsers/ie.png" alt="">
        </a>
    </div>
    <p>Sorry for the inconvenience!</p>
</div>
<![endif]-->

<!-- Javascript -->
<script src="{{ asset('js/app.js') }}?v={{ filemtime(base_path('public/js/app.js')) }}"></script>
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
@yield('scripts')
@include('analytics')
</body>
</html>
