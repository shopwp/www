<?php

$extensions = get_posts([
   'post_type' => 'download',
   'posts_per_page' => -1,
   'tax_query' => [
        [
            'taxonomy' => 'download_category',
            'terms' => 79,
            'include_children' => false
        ],
    ],
   'post_status' => 'publish',
   'no_found_rows' => true,
   'orderby' => 'menu'
]);

$styled = get_sub_field('styled');

include(locate_template('components/extensions/view.php'));