<section class="component component-marquee l-col l-col-center">

  <div class="marquee-content">

   <div class="logos">

      <img class="logo wordpress-logo" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-wp.svg" alt="WordPress Logo">
      <img class="logo shopify-logo" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-shopify.svg" alt="Shopify Logo">

    </div>

   <h1><?php the_sub_field('short_description'); ?></h1>
   
   
    <!-- <h1 class="marquee-heading"></h1>

    <div class="marquee-short-desc">
      <?php the_sub_field('short_description'); ?>
    </div> -->
    

    <div class="btn-group l-row l-row-center">
      <a href="https://demo.wpshop.io" class="btn btn-l btn-secondary btn-download-free">View the demo</a>
    </div>

  </div>
   <style>

      @keyframes MoveUpDown {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(40px);
  }
}

   .icon-test {
      max-width: 40px;
      position: absolute;
      filter: grayscale(1);
      /* filter: sepia(1); */
      opacity: 0.2;
      /* transform: rotate(-30deg); */
      /* filter: drop-shadow(6px 7px 2px rgba(0,0,0,0.15)); */
      /* animation: MoveUpDown 7s linear infinite; */

      display: none;

   }

   .icon-test-1 {
    top: 15vh;
    left: 5vw;
    transform: rotate(-45deg);
   }

   .icon-test-2 {
    top: 7vh;
    right: 9vw;
    transform: rotate(-18deg);
   }
   .icon-test-3 {
        top: -10px;
    left: 32vw;
    transform: rotate(-28deg);
   }
   .icon-test-4 {
    bottom: 111px;
    left: 11vw;

    transform: rotate(15deg);
   }

   .icon-test-5 {
        bottom: 80px;
    right: 46vw;
    transform: rotate(-15deg);
   }
    
      .icon-test-6 {
    right: 7vw;
    bottom: 58px;
    transform: rotate(14deg);
   }
      .icon-test-7 {
      bottom: 17vh;
      right: 22vw;
      transform: rotate(34deg);
   }
   

   </style>
  <img src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-test-1.svg" class="icon-test-1 icon-test">
<img src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-test-2.svg" class="icon-test-2 icon-test">

<img src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-test-3.svg" class="icon-test-3 icon-test">

<img src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-test-4.svg" class="icon-test-4 icon-test">

<!-- <img src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-test-5.svg" class="icon-test-5 icon-test"> -->

<img src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-test-6.svg" class="icon-test-6 icon-test">

<img src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-test-7.svg" class="icon-test-7 icon-test">
</section>

<!-- 
<section class="component component-snippet l-col l-col-center l-row-center">

  <div class="l-contain">
    <div class="snippet l-row">
      <div class="snippet-image">
        <img src="<?php echo get_template_directory_uri() ?>/assets/prod/imgs/illustration-brand.svg">
      </div>

      <div class="l-col l-row-center snippet-content">
        <h2 class="snippet-heading">Free your brand.</h2>
        <p class="snippet-copy">Keeping your brand consistent is important to build trust with your customers. Sync your data into WordPress without restrictive iFrames.</p>
      </div>
    </div>

  </div>

</section>

<section class="component component-snippet l-col l-col-center l-row-center">

  <div class="l-contain">

    <div class="snippet l-row">

      <div class="l-col l-row-center snippet-content">
        <h2 class="snippet-heading">Sync what you need,<br>display what you want.</h2>
        <p class="snippet-copy">You control the data and how it's shown to your users. We provide templates and over 100 + actions and filters.</p>

      </div>

      <div class="snippet-image">
        <img src="<?php echo get_template_directory_uri() ?>/assets/prod/imgs/illustration-sync.svg">
      </div>

    </div>

    <div class="snippet snippet-made-with-devs l-row">
      <div class="snippet-image">
        <img src="<?php echo get_template_directory_uri() ?>/assets/prod/imgs/illustration-code.svg">
      </div>

      <div class="l-col l-row-center snippet-content">
        <h2 class="snippet-heading">One plugin for two platforms.</h2>
        <p class="snippet-copy">Change something in Shopify and watch it automatically appear in WordPress. Webhooks are used to keep your data in sync.</p>

      </div>
    </div>

  </div>

</section> -->
