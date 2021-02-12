<?php

$show_all = get_sub_field('show_all');

if ($show_all) {

   $testimonials = get_posts([
      'post_type'       => 'testimonials',
      'numberposts'		=> -1,
   ]);

} else {

   $testimonials = get_posts([
      'post_type'       => 'testimonials',
      'numberposts'		=> 6,
      'orderby'         => 'menu_order',
      'order'         => 'ASC'
   ]);
}

include(locate_template('components/testimonials/view.php'));