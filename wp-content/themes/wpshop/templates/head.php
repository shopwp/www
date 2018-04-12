<head>

  <!--

  Only load drift on non-docs related pages

  -->
  <?php if ( get_post_type(get_the_ID()) !== 'docs') { ?>

    <!-- Start of Async Drift Code -->
    <script>
    !function() {
      var t;
      if (t = window.driftt = window.drift = window.driftt || [], !t.init) return t.invoked ? void (window.console && console.error && console.error("Drift snippet included twice.")) : (t.invoked = !0,
      t.methods = [ "identify", "config", "track", "reset", "debug", "show", "ping", "page", "hide", "off", "on" ],
      t.factory = function(e) {
        return function() {
          var n;
          return n = Array.prototype.slice.call(arguments), n.unshift(e), t.push(n), t;
        };
      }, t.methods.forEach(function(e) {
        t[e] = t.factory(e);
      }), t.load = function(t) {
        var e, n, o, i;
        e = 3e5, i = Math.ceil(new Date() / e) * e, o = document.createElement("script"),
        o.type = "text/javascript", o.async = !0, o.crossorigin = "anonymous", o.src = "https://js.driftt.com/include/" + i + "/" + t + ".js",
        n = document.getElementsByTagName("script")[0], n.parentNode.insertBefore(o, n);
      });
    }();
    drift.SNIPPET_VERSION = '0.3.1';
    drift.load('gddyshwbu3yh');
    </script>
    <!-- End of Async Drift Code -->

  <?php } ?>

  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl+ '&gtm_auth=zEmWFISEpQvchduPXr4jaQ&gtm_preview=env-2&gtm_cookies_win=x';f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-NWRL8QH');</script>
  <!-- End Google Tag Manager -->


  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="google-site-verification" content="tVVy_2muFLUdxErUb-Qi0BppnJRngpqGaiYoskxqtcc" />

  <?php wp_head(); ?>

  <?php if (get_post_type( get_the_ID() ) === 'docs') { ?>

    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-152x152.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon-32x32.png">

  <?php } else { ?>

    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon-76x76.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/android-chrome-192x192.png">
    <link rel="manifest" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="230x230" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon-230x230.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon-96x96.png">
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon.ico">
    <link rel="yandex-tableau-widget" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/yandex-browser-manifest.json">
    <meta name="msapplication-TileColor" content="#FFFFFF">
    <meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/mstile-144x144.png">

  <?php } ?>

</head>
