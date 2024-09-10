<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="<?= config('CURRENT_LOCALE_DIRECTION') ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<title>@yield('head-title') : <?= getStoreSettings('name') ?></title>
	<!-- Custom fonts for this template-->
	<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700&display=swap" rel="stylesheet">
	{{-- <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet"> --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fuzzy+Bubbles:wght@400;700&display=swap" rel="stylesheet">
	<link rel="shortcut icon" href="<?= getStoreSettings('favicon_image_url') ?>" type="image/x-icon">
	<link rel="icon" href="<?= getStoreSettings('favicon_image_url') ?>" type="image/x-icon">
	@if(getStoreSettings('allow_recaptcha'))
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	@endif

	<!-- Primary Meta Tags -->
	<meta name="title" content="@yield('page-title')">
	<meta name="description" content="@yield('description')">
	<meta name="keywordDescription" property="og:keywordDescription" content="@yield('keywordDescription')">
	<meta name="keywordName" property="og:keywordName" content="@yield('keywordName')">
	<meta name="keyword" content="@yield('keyword')">
	<!-- Google Meta -->
	<meta itemprop="name" content="@yield('page-title')">
	<meta itemprop="description" content="@yield('description')">
	<meta itemprop="image" content="@yield('page-image')">
	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="website">
	<meta property="og:url" content="@yield('page-url')">
	<meta property="og:title" content="@yield('page-title')">
	<meta property="og:description" content="@yield('description')">
	<meta property="og:image" content="@yield('page-image')">
	<!-- Twitter -->
	<meta property="twitter:card" content="@yield('twitter-card-image')">
	<meta property="twitter:url" content="@yield('page-url')">
	<meta property="twitter:title" content="@yield('page-title')">
	<meta property="twitter:description" content="@yield('description')">
	<meta property="twitter:image" content="@yield('page-image')">

	<!-- Custom styles for this template-->
	<?= __yesset([
		'dist/css/bootstrap-assets-app*.css',
		'dist/css/public-assets-app*.css',
		'dist/fa/css/all.min.css',
		"dist/css/vendorlibs-datatable.css",
		"dist/css/vendorlibs-photoswipe.css",
		"dist/css/vendorlibs-smartwizard.css",
		'dist/css/custom*.css',
		'dist/css/messenger*.css',
		'dist/css/login-register*.css'
	], true) ?>
    <style>
        body:not(.lw-ajax-form-ready) form.lw-ajax-form:before {
            content: "{{ __tr('please wait ...') }}";
        }
    </style>
	@stack('header')
</head>