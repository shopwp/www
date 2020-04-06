<head>
   
<?php wp_head(); ?>

<?php if (!is_page('checkout') && !is_page('account') && !is_page('affiliates')) { ?>

  <!--

  Only load drift on non-docs related pages

  -->

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


      <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light.css" />   
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/shift-toward.css" />

<script src="https://kit.fontawesome.com/4b023c7b57.js" crossorigin="anonymous"></script>

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

      <link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon-16x16.png">
   <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon-32x32.png">
   <link rel="shortcut icon" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/favicon.ico">
   <link rel="manifest" href="<?php echo get_template_directory_uri() ?>/assets/imgs/favicons/site.webmanifest">
    <meta name="mobile-web-app-capable" content="yes">

<?php if (!is_page('checkout')) { ?>
   <script src="https://unpkg.com/headroom.js"></script>
<?php } ?>

</head>
