<?php 

global $post;

?>
<article <?php post_class(); ?>>

  <header>

  <a href="<?php the_permalink(); ?>" class="post-link">

     <?php echo get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'alignleft' ) ); ?>
    <h2 class="entry-title"><?php the_title(); ?></h2>

    </a>
    <?php get_template_part('templates/entry-meta'); ?>
  </header>

  <div class="entry-summary">
    <?php the_excerpt(); ?>
  </div>
</article>
