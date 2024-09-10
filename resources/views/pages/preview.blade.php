<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="<?= config('CURRENT_LOCALE_DIRECTION') ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Primary Meta Tags -->
    <title>{{ getStoreSettings('name') }}</title>
    <meta name="title" content="{{ getStoreSettings('name') }}">
    <meta name="description" content="{{ getStoreSettings('name') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="{{ getStoreSettings('name') }}">
    <meta property="og:description" content="{{ getStoreSettings('name') }}">
    <meta property="og:image" content="{{ getStoreSettings('logo_image_url') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="{{ getStoreSettings('name') }}">
    <meta property="twitter:description" content="{{ getStoreSettings('name') }}">
    <meta property="twitter:image" content="{{ getStoreSettings('logo_image_url') }}">

    <?= __yesset([
        'dist/css/bootstrap-assets-app*.css',
        'dist/css/public-assets-app*.css',
        'dist/fa/css/all.min.css',
        'dist/css/home*.css',
        'dist/css/login-register*.css'
        ], true) ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet">

    <link rel="shortcut icon" href="<?= getStoreSettings('favicon_image_url') ?>" type="image/x-icon">
    <link rel="icon" href="<?= getStoreSettings('favicon_image_url') ?>" type="image/x-icon">
</head>

<body id="page-top" class="bg-dark lw-login-register-page">
    <!-- Navigation -->
@include('includes.outer-nav-bar')
<!-- Page Wrapper -->
   <!-- Begin Page Content -->
   <div class="lw-page-content lw-other-page-content">
    <section class="section">
        <div class="container">
            @if(isset($page) and !__isEmpty($page))
            <div class="row">
                <div class="content-area col-md-12 mx-auto">
                    <div class="site-content" role="main">
                        <article>
                            <header>
                                <h1><?= $page['title'] ?></h1>
                            </header>
                            <div class="lw-page-description">
                                <?= $page['content'] ?>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
    <?= __yesset(
      [
          'dist/js/vendorlibs-public.js',
      ],
      true,
  ) ?>

    <script>
        (function($) {
            "use strict"; // Start of use strict

            // Smooth scrolling using jQuery easing
            $('a.js-scroll-trigger[href*="#"]:not([href="#"])').click(function() {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location
                    .hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        $('html, body').animate({
                            scrollTop: (target.offset().top - 70)
                        }, 1000, "easeInOutExpo");
                        return false;
                    }
                }
            });

            // Closes responsive menu when a scroll trigger link is clicked
            $('.js-scroll-trigger').click(function() {
                $('.navbar-collapse').collapse('hide');
            });

            // Activate scrollspy to add active class to navbar items on scroll
            $('body').scrollspy({
                target: '#mainNav',
                offset: 100
            });

            // Collapse Navbar
            var navbarCollapse = function() {
                if ($("#mainNav").offset().top > 100) {
                    $("#mainNav").addClass("navbar-shrink");
                } else {
                    $("#mainNav").removeClass("navbar-shrink");
                }
            };
            // Collapse now if page is not at top
            navbarCollapse();
            // Collapse the navbar when page is scrolled
            $(window).scroll(navbarCollapse);

        })(jQuery); // End of use strict
    </script>

</body>
</html>
