<?php 

$detect = $GLOBALS['is_mobile'];

$is_mobile = $detect->isMobile();

?>

<section class="component component-marquee l-col">

  <div class="marquee-content l-contain-wide">

      <svg class="screen-two" id="10015.io" viewBox="0 0 480 480" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" >
	<path fill="#dec1ff" d="M447.5,295Q450,350,411,390.5Q372,431,318,435.5Q264,440,231.5,393.5Q199,347,179.5,329Q160,311,103.5,299Q47,287,64.5,244Q82,201,84.5,153Q87,105,125.5,71.5Q164,38,215.5,28Q267,18,297,66Q327,114,377,128Q427,142,436,191Q445,240,447.5,295Z" />
</svg>

      <div class="marquee-left">

         <h1>Display and sell Shopify products on WordPress.</h1>
         <p class="marquee-short-blurb" style="max-width:762px;">ShopWP is a WordPress eCommerce plugin allowing creators, founders, and merchants to sell Shopify products on any WordPress site.</p>

         <p class="marquee-short-blurb">Join the 5000+ community of entrepreneurs currently using ShopWP to make money online!</p>

         <div class="btn-group l-row l-row-left">
            <a href="/purchase/" class="btn btn-download-free">Buy ShopWP Pro 6.0</a>
            <span class="marquee-or">or</span>
            <a href="#!" class="btn btn-secondary btn-demo-click">View a live demo 🕹</a>
            <svg style="width: 200px;position: absolute;right: -220px;bottom: -15px;transform: rotate(18deg);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 174 76" style="enable-background:new 0 0 174 76" xml:space="preserve"><path d="M11.1 69.5c-.1.4-.2.8.1 1 .4.3.7 0 1-.3.6-.7 1.2-1.5 1.8-2.3 3.9-5.8 7.7-11.7 11.3-17.8 5.2-8.9 12.2-16.3 19.3-23.7 1.9-1.9 3.8-3.7 6-5.3.4-.3.7-.8 1.4-.5-.1.4-.2.9-.3 1.3-1.3 4.3-2.7 8.6-4 12.9-1.6 5.2-3.2 10.3-4 15.7-.6 3.3-.7 6.5.6 9.5 1.3 2.9 3.6 3.8 6.6 2.6 1.4-.6 2.6-1.5 3.8-2.3 7-5 13-11.2 19.4-16.9 10-8.9 21-16.3 33.6-21.1 7.8-3 15.9-5.3 24.3-6 8-.6 16 .1 24.1.5-.3 1-1.1 1.2-1.7 1.6-1.3.9-2.5 1.8-3.6 2.9-.4.4-.7 1-.4 1.6.3.7 1 .9 1.7.7.9-.2 1.7-.5 2.5-.8 4.3-1.8 8.1-4.6 12.4-6.4 1.6-.6 2-2.1 1.6-3.7-.4-1.7-1.5-2.4-3-2.9-3.9-1.2-7.8-2.3-11.6-3.9-1-.4-2-.6-3-.9-.8-.2-1.7-.2-2.2.7-.6 1 0 1.7.7 2.3.7.7 1.6 1.3 2.5 1.8 1 .6 2 1.2 3.3 2-2.3 0-4.3.1-6.1 0-7.2-.5-14.5-.7-21.7.3-19.5 2.8-36.5 11-51.6 23.2-4.6 3.7-8.8 7.9-13.1 12-3.6 3.4-7.3 6.7-11.2 9.7-.5.4-1.1.8-1.6 1.1-1.1.6-1.8.3-2.2-.9-.2-.5-.2-1-.3-1.5-.1-2.1-.1-4.3.3-6.4.6-3.6 1.6-7.2 2.6-10.8 1.6-5.5 3.3-10.9 4.9-16.4.3-1.1.6-2.1.6-3.3-.1-2.3-1.9-3.4-4-2.5-1.4.6-2.5 1.5-3.6 2.5-8.3 7.5-15.8 15.4-21.9 24.5-5.4 8.1-9.9 16.8-14.9 25.2-.1.3-.3.6-.4 1z" style="fill:#141414"/></svg>
         </div>

         <img class="icon-logo-wordpress" src="<?php echo get_template_directory_uri() ?>/assets/imgs/logo-wordpress.svg" alt="WordPress" />

      </div>

      <?php if (!$is_mobile) { ?>
         <div class="marquee-right">

            <div class="inner">

               <div class="live-screen">
                  <?= do_shortcode('[wps_products title="Super awesome t-shirt" variant_style="buttons" show_zoom="true" show_compare_at="true" subscriptions="true"]'); ?>
               </div>

               <img class="icon-game" src="<?php echo get_template_directory_uri() ?>/assets/imgs/game.svg" alt="Example product customization options for WordPress eCommerice plugin" />

               <img class="icon-game2" src="<?php echo get_template_directory_uri() ?>/assets/imgs/game2.svg" alt="Example product customization options for WordPress eCommerice plugin" />

               <img class="icon-game3" src="<?php echo get_template_directory_uri() ?>/assets/imgs/game3.svg" alt="Example product customization options for 
               WordPress eCommerice plugin" />

               <img class="icon-game5" src="<?php echo get_template_directory_uri() ?>/assets/imgs/game5.svg" alt="Example product customization options for 
               WordPress eCommerice plugin" />
<!-- 
               <img class="icon-game4" src="<?php echo get_template_directory_uri() ?>/assets/imgs/game4.svg" alt="Example product customization options for WordPress eCommerice plugin" /> -->

            </div>

         </div>

      <?php } ?>

      </div>

      

   </div>

</section>

<section class="component-new-notice">
   <div class="l-contain-wide">
      <p><a href="/purchase"><span>🎉</span> ShopWP 6.0 is here!</a> Try out the brand new syncing and native WordPress integration</p>
   </div>
</section>

<section class="component-syncing">
   <div class="l-contain-wide">
      <svg class="game7"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100" xml:space="preserve"><path d="M42.5 80c-.1-2.8 1.5-5.1 3.6-5.5 2.2-.4 4.5-.5 6.8-.9 1.4-.2 2.6-.9 4-1.3 2.5-.7 5-1.3 7.6-1.8 2.7-.5 5.4-1 8.2-1.3l14.4-1.8c2.4-.3 4.8-.8 7.1-.6 2.7.2 4.7 3.7 3.8 6.3-.1.3-.5.7-.8.8-2.4 1.4-4.7 2.4-7.7 1.9-2.3-.3-4.7.5-7 .8-4.9.7-9.8 1.5-14.7 2.2-3.8.6-7.5 1.6-11.3 1.5-1.7-.1-3 .9-4.7.6-1.8-.3-3.7.6-5.6.7-1.8.1-3.3-.5-3.7-1.6zM61.7 21.8c.6-.4 1.2-.8 1.8-1.3.6 1.4 3 1.8 2.6 3.9-.3 1.4 0 3.3-2.4 2.9.9 3.3-1.9 4.3-3.3 5.9-1.8 2.1-3.8 4-5.8 5.8-2.9 2.7-6 5.2-8.8 8-1.2 1.2-1.8 2.9-3 4.1-.8.9-.6 2.2-2.6 2.6-2.9.6-2.8 1.1-3.9.6-2.5-1.3-3-4.4-.9-7.3 2.6-3.6 5.5-7 8.9-10 3.5-3.1 6.6-6.7 9.8-10.2 1.4-1.5 2.8-3.1 4.4-4.5.8-.6 2-.7 3.1-1 0 .3 0 .4.1.5zM13.6 48.6c-2.5 0-4.4-2.9-3.9-5.9 1.1-6.6 2.6-13.1 3.1-19.8.2-2.9.2-5.7 1.1-8.4 1-3.1 2.7-4.6 4.8-4.2 3.2.6 4.3 3.6 4 6.3-.7 6.1-1.7 12.1-2.8 18.2-.7 3.7-1.8 7.4-2.9 11-.6 1.4-1.3 3.2-3.4 2.8z"/></svg>
      <div class="l-row">
         
         <div class="l-box-2">
            <img class="screen" src="<?php echo get_template_directory_uri() ?>/assets/imgs/syncing-screenshot.png" alt="ShopWP syncing example" />
         </div>
         <div class="l-box-2" style="padding-left: 60px;">
            <h2 style="margin-top: 40px;">Sync your Shopify data directly into WordPress <span>✅</span></h2>
            <br>
            <p>Images, metafields, collections&mdash;ShopWP can sync it all.</p>
            <p>Use your Shopify data directly with other plugins such as Elementor, Yoast, etc. Because the data is synced as post meta values, the sky is the limit for the type of integrations you can create.</p>
            <p>ShopWP allows you to sync products by collections, tags, vendors, or any combination. This allows you to sync only what you need&mdash;nothing more.<p>
         </div>
      </div>
   </div>
</section>

<script>
   
   jQuery('.btn-demo-click').on('click', function(e) {

      e.preventDefault();

      jQuery('.live-screen').addClass('anime-grow');
      setTimeout(function() {
         jQuery('.live-screen').removeClass('anime-grow');
      }, 800);

   });

</script>