<section class="component component-extensions" data-is-styled="<?= $styled; ?>">

   <?php if ($styled) { ?>
      <div class="l-contain l-center">
         <h2>Extensions for WP Shopify Pro</h2>
         <p style="font-size:17px;margin: 0 auto;max-width: 650px;">Want to take your store to the next level? Take a look at our official WP Shopify Pro extensions below. Guaranteed to give your store super powers. 😃</p><br><br>
      </div>
   <? } ?>

   <div class="l-contain l-row">

      <?php foreach ($extensions as $extension) { ?>

      <div class="extension-wrapper">
         <div class="extension-image">
            <a href="<?= $extension->guid; ?>" class="extension-link">
               <img src="<?= get_the_post_thumbnail_url($extension->ID, 'large'); ?>" alt="<?= $extension->post_title ?>" />
            </a>
         </div>

         <div class="extension-inner">
            <a class="extension-name" href="<?= $extension->guid; ?>"><?= $extension->post_title ?></a>
            <div class="extension-excerpt"><?= get_the_excerpt($extension->ID); ?></div>
            <a class="extension-cta btn" href="<?= $extension->guid; ?>">View details</a>
         </div>
         
      </div>

      <?php } ?>
   </div>
</section>