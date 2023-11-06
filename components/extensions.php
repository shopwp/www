<?php

$extensions = get_posts([
   'post_type' => 'download',
   'posts_per_page' => -1,
   'tax_query' => [
        [
            'taxonomy' => 'download_category',
            'terms' => 80,
            'include_children' => false
        ],
    ],
   'post_status' => 'publish',
   'no_found_rows' => true,
   'orderby' => 'menu',
   'ignore_sticky_posts' => true
]);

?>

<section class="component component-extensions">

    <ul class="l-contain l-row l-row-center l-list">

        <?php foreach ($extensions as $extension) { ?>
            
            <li class="extension card">
                <a href="/extensions/<?= $extension->post_name; ?>/" class="extension-link">
                    <div class="extension-image">
                        <img src="<?= get_the_post_thumbnail_url($extension->ID, 'large'); ?>" alt="<?= $extension->post_title ?>" />
                    </div>

                    <div class="extension-inner">
                        <h2 class="extension-name"><?= $extension->post_title ?></h2>
                        <div class="extension-excerpt"><?= get_the_excerpt($extension->ID); ?></div>
                    </div>
                </a>
            </li>

        <?php } ?>

   </ul>
</section>