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

      }

    endwhile;

  else:

  endif;

?>
