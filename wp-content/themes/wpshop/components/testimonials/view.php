<section class="component component-testimonials">
   <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 962 115" xml:space="preserve" preserveAspectRatio="none" class="svg replaced-svg">
   <path class="st0" d="M0,0c0,0,100,94,481,95C862,94,962,0,962,0v115H0V0z"></path>
   </svg>

  <h2>Over 6,000+ WordPress sites are currently using WP Shopify. Here's what they have to say!</h2>

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
      <a class="btn" href="/testimonials">View all testimonials</a>
   </div>
  <?php } ?>

  
</section>