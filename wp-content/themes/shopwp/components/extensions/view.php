<section class="component component-extensions" data-is-styled="<?= $styled; ?>">

   <?php if ($styled) { ?>
      <div class="l-contain l-center">
         <h2>Extensions for ShopWP Pro</h2>
         <p style="font-size:18px;margin: 0 auto;max-width: 730px;">Our extensions for ShopWP Pro will give your WordPress eCommerce store super powers. If you purchase the Agency plan, you can have them all for free!</p><br><br>
      </div>
   <? } ?>

   <div class="l-contain l-row">

      <?php foreach ($extensions as $extension) { ?>

      <div class="extension-wrapper">
         <div class="extension-image">
            <a href="/extensions/<?= $extension->post_name; ?>/" class="extension-link">
               <img src="<?= get_the_post_thumbnail_url($extension->ID, 'large'); ?>" alt="<?= $extension->post_title ?>" />
            </a>
         </div>

         <div class="extension-inner">
            <a class="extension-name" href="<?= $extension->guid; ?>"><?= $extension->post_title ?></a>
            <div class="extension-excerpt"><?= get_the_excerpt($extension->ID); ?></div>
            <a class="extension-cta btn" href="/extensions/<?= $extension->post_name; ?>/">View details</a>
         </div>
         
      </div>

      <?php } ?>
   </div>
</section>