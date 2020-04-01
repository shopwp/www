<section class="component component-marquee l-col">

  <div class="marquee-content l-row">

  <div class="marquee-left">
         
         <div class="logos-wrapper">
            
            <a class="logos" target="_blank" href="https://www.shopify.com/?ref=wps">
               <img class="logo wordpress-logo" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-wp.svg" alt="WordPress Logo">
               <img class="logo shopify-logo" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-shopify.svg" alt="Shopify Logo">
            </a>

            <?php include(locate_template('components/current-version/view.php')); ?>

         </div>

         <h1><?php the_sub_field('short_description'); ?></h1>
         
         <p class="marquee-short-blurb">A powerful WordPress plugin that enables creators, businesses, and developers to sell Shopify products on any WordPress site.</p>

         <div class="btn-group l-row l-row-left">
            <span class="btn btn-download-free getting-started-trigger" target="_blank">Start for free</span>
            <a href="https://demo.wpshop.io" class="btn btn-l btn-secondary" target="_blank">View the demo <svg class="svg-inline--fa fa-external-link fa-w-16" aria-hidden="true" focusable="false" data-prefix="fal" data-icon="external-link" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M440,256H424a8,8,0,0,0-8,8V464a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V112A16,16,0,0,1,48,96H248a8,8,0,0,0,8-8V72a8,8,0,0,0-8-8H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V264A8,8,0,0,0,440,256ZM500,0,364,.34a12,12,0,0,0-12,12v10a12,12,0,0,0,12,12L454,34l.7.71L131.51,357.86a12,12,0,0,0,0,17l5.66,5.66a12,12,0,0,0,17,0L477.29,57.34l.71.7-.34,90a12,12,0,0,0,12,12h10a12,12,0,0,0,12-12L512,12A12,12,0,0,0,500,0Z"></path></svg></a>
         </div>

      </div>

      <div class="marquee-right">

      <img class="screen-one" src="<?php echo get_template_directory_uri() ?>/assets/imgs/marquee-right-<?= rand(1, 5) ?>.jpg" alt="Sup">

      <img class="screen-two" src="<?php echo get_template_directory_uri() ?>/assets/imgs/marquee-right-two.jpg" alt="Sup">
      <div class="stage"></div>
      </div>
  </div>

  

</div>


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
