<?php

  if(have_rows('components')):

    while(have_rows('components')) : the_row();

      // Mailing List
      if(get_row_layout() == 'component_mailinglist') {

        get_template_part('components/mailinglist/mailinglist-controller');

      // Details
      } else if(get_row_layout() == 'component_marquee') {

        get_template_part('components/marquee/marquee-controller');

      // Products
      } else if(get_row_layout() == 'component_products') {

        get_template_part('components/products/products-controller');

      // Products
      } else if(get_row_layout() == 'component_support') {

        get_template_part('components/support/support-controller');

      // Features
      } else if(get_row_layout() == 'component_features') {

        get_template_part('components/features/features-controller');

      }

    endwhile;

  else:

  endif;

?>
