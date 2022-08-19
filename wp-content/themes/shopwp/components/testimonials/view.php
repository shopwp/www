<section class="component component-testimonials" style="<?= is_page('testimonials') ? '' : 'background:white'; ?>">

<?php if (!is_page('testimonials')) { ?>
   <div class="stage">
      <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 962 115" xml:space="preserve" preserveAspectRatio="none" class="svg replaced-svg">
         <path d="M0,0c0,0,100,94,481,95C862,94,962,0,962,0v115H0V0z"></path>
      </svg>
   </div>
<?php } ?>
   <?php if (is_page('testimonials')) { ?>
      <h1 style="max-width: 800px;margin: 20px auto 40px auto;text-align: center;">Over 5,000+ WordPress sites are currently using ShopWP.<br>Here's what they have to say!</h1>
   <?php } else { ?>

      <h3 style="font-size: 40px;margin: 0 auto 1em;max-width: 920px;padding-top: 0;text-align: center;">Over 5,000+ WordPress sites are currently using ShopWP. Here's what they have to say!</h3>
   <?php } ?>

  <div class="testimonials">
     <div class="grid-sizer"></div>
  <?php 
  
  foreach ($testimonials as $testimonial) {

      $testimonial_type = get_field('testimonial_type', $testimonial->ID);
      $testimonial_author_name = get_field('testimonial_author_name', $testimonial->ID);
      $testimonial_content = get_field('testimonial_content', $testimonial->ID);

      if ($testimonial_type === 'wp') {
         
         $testimonial_heading = get_field('testimonial_heading', $testimonial->ID);
         include(locate_template('components/testimonials/card-wp.php'));

      } else {

         $testimonial_author_title = get_field('testimonial_author_title', $testimonial->ID);
         $testimonial_author_image = get_field('testimonial_author_image', $testimonial->ID);
         $testimonial_author_link = get_field('testimonial_author_link', $testimonial->ID);

         include(locate_template('components/testimonials/card-personal.php'));
      }
  }

  
  ?>

  </div>

  <?php if (!$show_all) { ?>
   <div class="l-row l-row-center">
      <a class="btn btn-l" href="/testimonials/">View all testimonials</a>
   </div>
  <?php } ?>

  
</section>