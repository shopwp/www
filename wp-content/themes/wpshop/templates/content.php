<?php 

global $post;

?>

<article <?php post_class(); ?>>

  <header>

  <a href="<?php the_permalink(); ?>" class="post-link">

   <div class="post-thumb" style="background-image: url('<?= get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');"></div>

      <h2 class="entry-title"><?php the_title(); ?></h2>

    </a>
    <?php get_template_part('templates/entry-meta'); ?>
  </header>

  <div class="entry-summary">
    <?php the_excerpt(); ?>
  </div>
  
</article>
