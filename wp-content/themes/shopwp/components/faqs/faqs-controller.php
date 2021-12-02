<?php

$faqs_type = get_sub_field('faqs_type');

if ($faqs_type === 'primary') {

      $args2 = array(
      'type'                     => 'post',
      'taxonomy'                 => 'faq-category',
      'pad_counts'               => false
   );

   $categories = get_categories( $args2 );
   $faqs = [];

   foreach ( $categories as $cat ) {

   $args = array(
      'posts_per_page' => -1,
      'post_type' => 'faqs',
      'tax_query' => array(
            array(
               'taxonomy' => 'faq-category',
            'field' => 'name',
            'terms' => $cat->name
            )
         )
         );

      $faqs[$cat->name] = get_posts($args);

   }

   include(locate_template('components/faqs/faqs-view.php'));

} else {

   $faqs_selected = get_sub_field('faqs');

   include(locate_template('components/faqs/secondary.php'));
}

?>
