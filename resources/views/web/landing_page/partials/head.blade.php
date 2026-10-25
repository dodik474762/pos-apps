<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <title>{{ $pageTitle ?? 'DMS — Software Distribusi' }}</title>
    <meta name="description" content="{{ $pageDescription ?? 'Platform distribusi untuk mengelola order, stok, salesman, piutang, dan pengiriman dalam satu dashboard.' }}">
    <meta name="keywords" content="{{ $pageKeywords ?? 'software distribusi, aplikasi distributor' }}">
    <link rel="canonical" href="{{ $canonical ?? url('/landing') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DMS">
    <meta property="og:title" content="{{ $pageTitle ?? 'DMS — Software Distribusi' }}">
    <meta property="og:description" content="{{ $pageDescription ?? 'Platform distribusi untuk mengelola order, stok, salesman, piutang, dan pengiriman dalam satu dashboard.' }}">
    <meta property="og:url" content="{{ $canonical ?? url('/landing') }}">
    <meta property="og:image" content="{{ asset('assets/images/logo-main-app.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle ?? 'DMS — Software Distribusi' }}">
    <meta name="twitter:description" content="{{ $pageDescription ?? 'Platform distribusi untuk mengelola order, stok, salesman, piutang, dan pengiriman dalam satu dashboard.' }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-main-app-v2.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/landing_page/css/landing.css') }}">
    <script>document.documentElement.classList.add('js');</script>
    @stack('head')
</head>
<body class="{{ $bodyClass ?? '' }}">
<a class="skip-link" href="#main">Lewati ke konten</a>
@include('web.landing_page.partials.icons')
