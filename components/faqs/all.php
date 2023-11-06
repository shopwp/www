<section class="component component-faq l-contain-s wrap">

  <?php 
  
  foreach ($faqs_all as $cat => $value) { ?>

    <div class="faqs-group">

      <h2 class="faqs-heading"><?php echo $cat; ?></h2>

      <?php 

      $faqs = $faqs_all[$cat];
      
      include(locate_template('components/faqs/content.php'));

      ?>
      
    </div>

  <?php } ?>

</section>
