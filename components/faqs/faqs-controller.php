<?php

$faqs_type = get_sub_field('faqs_type');

if ($faqs_type === 'primary') {

      $args2 = array(
      'type'                     => 'post',
      'taxonomy'                 => 'faq-category',
      'pad_counts'               => false
   );

   $categories = get_categories( $args2 );
   $faqs_all = [];

   foreach ( $categories as $cat ) {

      $args = [
         'posts_per_page'        => -1,
         'post_type'             => 'faqs',
         'ignore_sticky_posts'   => true,
         'post_status'           => 'publish',
         'tax_query' => [
            [
               'taxonomy'  => 'faq-category',
               'field'     => 'name',
               'terms'     => $cat->name
            ]
         ]
      ];

      $faqs_all[$cat->name] = get_posts($args);

   }

   include(locate_template('components/faqs/all.php'));

} else {

   $faqs_selected = get_sub_field('faqs');

   $faqs = get_posts([
      'post__in'              => $faqs_selected,
      'post_type'             => 'faqs',
      'nopaging'              => true,
      'ignore_sticky_posts'   => true,
      'post_status'           => 'publish'
   ]);

   include(locate_template('components/faqs/secondary.php'));
}

?>
