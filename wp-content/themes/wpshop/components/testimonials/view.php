<section class="component component-testimonials">
  <h2>Over 4,000+ WordPress sites are currently using WP Shopify. Here's what they have to say.</h2>

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
</section>