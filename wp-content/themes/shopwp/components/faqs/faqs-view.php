<section class="component component-faq l-contain-narrow wrap">

  <?php 
  
  foreach ($faqs as $cat => $value) { ?>

    <div class="faqs-group">

      <h2 class="faqs-heading" id="<?php echo formatHashSlug($cat); ?>"><?php echo $cat; ?></h2>

      <?php 
      
      $faq_ids = array_map(function($faq) {
         return $faq->ID;
      }, $faqs[$cat]);
      
      include(locate_template('components/faqs/content.php'));

      ?>
      
    </div>

  <?php } ?>

</section>
