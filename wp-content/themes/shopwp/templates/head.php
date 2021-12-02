<head>

<link rel="preconnect" href="https://cdn.shopify.com" crossorigin>
<link rel="dns-prefetch" href="https://cdn.shopify.com">

<link rel="preconnect" href="https://wpstest.myshopify.com" crossorigin>
<link rel="dns-prefetch" href="https://wpstest.myshopify.com">

<link rel="preconnect" href="https://scripts.blog" crossorigin>
<link rel="dns-prefetch" href="https://scripts.blog">

<link rel="preconnect" href="https://fonts.gstatic.com">

<?php wp_head(); ?>

<?php if (!is_page('checkout') && !is_page('purchase-confirmation') && !is_page('account') && !is_page('affiliates')) { ?>

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

    <meta name="mobile-web-app-capable" content="yes">

<?php if (!is_page('checkout') && !is_page('purchase-confirmation')) { ?>
   <script src="https://unpkg.com/headroom.js"></script>
<?php } ?>


</head>
