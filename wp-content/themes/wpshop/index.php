<?php 

global $post;

get_template_part('templates/page', 'header'); ?>

<?php if (!have_posts()) : ?>
  <div class="alert alert-warning">
    <?php _e('Sorry, no results were found.', 'sage'); ?>
  </div>
  <?php get_search_form(); ?>
<?php endif; ?>


<?php if ($post->post_type === 'post') { ?>
<div class="articles-wrapper">
<?php } ?>

<?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/content', get_post_type() != 'post' ? get_post_type() : get_post_format()); ?>
<?php endwhile; ?>

<?php if ($post->post_type === 'post') { ?>
</div>
<?php } ?>

<?php the_posts_navigation(); ?>
