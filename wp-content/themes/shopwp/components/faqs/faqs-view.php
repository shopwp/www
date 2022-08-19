<section class="component component-faq l-contain-narrow wrap">

  <div class="stage">
      <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 962 115" xml:space="preserve" preserveAspectRatio="none" class="svg replaced-svg">
         <path d="M0,0c0,0,100,94,481,95C862,94,962,0,962,0v115H0V0z"></path>
      </svg>
   </div>
   
  <?php 
  
  foreach ($faqs_all as $cat => $value) { ?>

    <div class="faqs-group">

      <h2 class="faqs-heading" id="<?php echo formatHashSlug($cat); ?>"><?php echo $cat; ?></h2>

      <?php 

      $faqs = $faqs_all[$cat];
      
      include(locate_template('components/faqs/content.php'));

      ?>
      
    </div>

  <?php } ?>

</section>
