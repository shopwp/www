<?php

/*

Template Name: Landing Page

*/

?>

<?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/page', 'header'); ?>
  <?php get_template_part('templates/content', 'page'); ?>
<?php endwhile; ?>

<!-- <section class="component">
  <a href="#" class="btn btn-lg">Learn more</a>
</section> -->
