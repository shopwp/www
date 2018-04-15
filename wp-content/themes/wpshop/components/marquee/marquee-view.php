<section class="component component-marquee l-col l-col-center">

  <div class="marquee-content">

    <h1 class="marquee-heading"><span class="wordpress">WordPress</span> <i class="fas fa-sync-alt fa-spin"></i> <span class="shopify">Shopify</span></h1>

    <div class="marquee-short-desc">
      <?php the_sub_field('short_description'); ?>
    </div>

    <div class="btn-group l-row l-row-center">
      <a href="/demo" class="btn btn-l btn-secondary" data-gtm="learn-home"><i class="fas fa-download"></i> Free trial</a>
      <a href="/purchase" class="btn btn-l" data-gtm="purchase-home"><i class="fas fa-cart-plus"></i> Purchase</a>
    </div>
    
  </div>

  <!-- <img src="<?php the_sub_field('image'); ?>" alt="" class="marquee-img"> -->

</section>


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

</section>
