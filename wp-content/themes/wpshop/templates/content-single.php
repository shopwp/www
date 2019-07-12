<?php while (have_posts()) : the_post(); ?>

  <article <?php post_class(); ?>>

    <header>

         <?php echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'alignleft' ) ); ?>
        <h1 class="entry-title"><?php the_title(); ?></h1>

        <?php get_template_part('templates/entry-meta'); ?>
      </header>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>
    <footer>
      <?php wp_link_pages(['before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']); ?>
    </footer>

    <?php comments_template('/templates/comments.php'); ?>

  </article>

  <div class="article-footer">
    <a href="<?= esc_url(home_url('/purchase')); ?>" rel="author" class="fn post-logo-link" target="_blank">
      <img src="<?php the_field('theme_logo_mark', 'option'); ?>" alt="WP Shopify" class="logo-header">
    </a>
  </div>

<?php endwhile; ?>
