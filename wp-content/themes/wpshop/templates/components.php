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

      // FAQs
      } else if(get_row_layout() == 'component_faqs') {

        get_template_part('components/faqs/faqs-controller');

      // Downloads
      } else if(get_row_layout() == 'component_downloads') {

        get_template_part('components/downloads/downloads-controller');

      // Changelog
      } else if(get_row_layout() == 'component_changelog') {

        get_template_part('components/changelog/changelog-controller');

      // Screenshots
      } else if(get_row_layout() == 'component_screenshots') {

        get_template_part('components/screenshots/screenshots-controller');

      // Screenshots Secondary
      } else if(get_row_layout() == 'component_screenshots_secondary') {

        get_template_part('components/screenshots-secondary/screenshots-secondary-controller');

      // Screenshots
      } else if(get_row_layout() == 'component_pro') {

        get_template_part('components/pro/pro-controller');

      // Affiliates
      } else if(get_row_layout() == 'component_affiliate') {

        get_template_part('components/affiliate/affiliate-controller');

      // How it works
      } else if(get_row_layout() == 'component_how_it_works') {

        get_template_part('components/how-it-works/how-it-works-controller');
      
        // Examples
      } else if(get_row_layout() == 'component_examples') {

        get_template_part('components/examples/examples-controller');

      // Pro features
      } else if(get_row_layout() == 'component_pro_features') {

        get_template_part('components/pro-features/pro-features-controller');

      // Pro features
      } else if(get_row_layout() == 'component_affiliate_register') {

        get_template_part('components/affiliate-register/controller');

      // Comparison Chart
      } else if(get_row_layout() == 'component_comparison_chart') {

        get_template_part('components/comparison-chart/controller');

      // Testimonials
      } else if(get_row_layout() == 'component_testimonials') {

        get_template_part('components/testimonials/controller');

      }
      
    endwhile;

  else:

  endif;

?>
