<head>

   <link rel="preconnect" href="https://fonts.gstatic.com">
   <link href="//fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet">

   <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;700&display=swap" rel="stylesheet">

   <style>

      @font-face {
      font-family: 'Metropolis';
      src: url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-medium-webfont.woff2') format('woff2'),
         url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-medium-webfont.woff') format('woff');
      font-weight: normal;
      font-style: normal;
      }

      @font-face {
      font-family: 'Metropolis';
      src: url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-extrabold-webfont.woff2') format('woff2'),
         url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-extrabold-webfont.woff') format('woff');
      font-weight: 500;
      font-style: normal;
      }

      @font-face {
      font-family: 'Metropolis';
      src: url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-bold-webfont.woff2') format('woff2'),
         url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-bold-webfont.woff') format('woff');
      font-weight: 600;
      font-style: normal;
      }

      @font-face {
      font-family: 'Metropolis';
      src: url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-black-webfont.woff2') format('woff2'),
         url('<?= get_template_directory_uri() . '/assets/fonts/' ?>/metropolis-black-webfont.woff') format('woff');
      font-weight: 800;
      font-style: normal;
      }

      html.wps-account{height:100%}body{letter-spacing:.01em;line-height:1.5;height:100%;font-family:Inter,arial}h1,h2,h3{font-family:Metropolis,helvetica}#root{height:100%}a{text-decoration:underline}a,a:visited{color:#000}a:hover{cursor:pointer}.App-logo{height:40vmin;pointer-events:none}@media (prefers-reduced-motion:no-preference){.App-logo{-webkit-animation:App-logo-spin infinite 20s linear;animation:App-logo-spin infinite 20s linear}}nav .link{margin:0;border:none;text-decoration:none;font-size:1em;color:#0f0311;padding:1.2em 1.1em}.App-link{color:#61dafb}@-webkit-keyframes App-logo-spin{from{-webkit-transform:rotate(0);transform:rotate(0)}to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes App-logo-spin{from{-webkit-transform:rotate(0);transform:rotate(0)}to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}.ReactModal__Content{opacity:0;-webkit-transform:translateY(-50px);-ms-transform:translateY(-50px);transform:translateY(-50px);-webkit-transition:all .25s ease;-o-transition:all .25s ease;transition:all .25s ease}.ReactModal__Content--after-open.ReactModal__Content--before-close,.ReactModal__Content--before-close{opacity:0;-webkit-transform:translateY(-50px);-ms-transform:translateY(-50px);transform:translateY(-50px)}.ReactModal__Overlay.ReactModal__Overlay--after-open{opacity:1;-webkit-transition:all .25s ease;-o-transition:all .25s ease;transition:all .25s ease}.ReactModal__Overlay--after-open.ReactModal__Overlay--before-close{opacity:0}.ReactModal__Content--after-open{opacity:1;-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}

   </style>

   <?php wp_head(); ?>

</head>