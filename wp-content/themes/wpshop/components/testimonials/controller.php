<?php

$testimonials = get_posts([
   'post_type'       => 'testimonials',
   'numberposts'		=> -1,
]);

include(locate_template('components/testimonials/view.php'));