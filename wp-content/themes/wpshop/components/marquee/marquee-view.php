<section class="component component-marquee l-col l-col-center">

  <div class="marquee-content">

    <h1 class="marquee-heading"><span class="wordpress">WordPress</span> <i class="fa fa-refresh fa-spin fa-fw"></i> <span class="shopify">Shopify</span></h1>

    <div class="marquee-short-desc l-contain-narrow">
      <?php the_sub_field('short_description'); ?>
    </div>

    <div class="btn-group l-row l-row-center">
      <a href="/how" class="btn btn-l btn-secondary"><i class="fa fa-book" aria-hidden="true"></i> Learn more</a>
      <a href="/purchase" class="btn btn-l"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Purchase</a>
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
        <p class="snippet-copy">Keeping your brand consistent is important to build trust with your customers. WP Shopify syncs your data directly into WordPress without restrictive iFrames giving you complete control over styling.</p>
      </div>
    </div>

  </div>

</section>

<section class="component component-snippet l-col l-col-center l-row-center">

  <div class="l-contain">

    <div class="snippet l-row">

      <div class="l-col l-row-center snippet-content">
        <h2 class="snippet-heading">Sync what you need,<br>display what you want.</h2>
        <p class="snippet-copy">WP Shopify is built around hooks and has an action or filter for everything. You control the data and how it's shown to your users.</p>

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
        <p class="snippet-copy">Webhooks are used under the hood to keep your data in sync. Change something in Shopify and watch it automatically appear in WordPress.</p>

      </div>
    </div>

  </div>

</section>
