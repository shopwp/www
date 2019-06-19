<article <?php post_class(); ?>>

  <header>
    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
  </header>

  <div class="entry-summary">
    <?php the_field('faq_answer', get_the_ID()); ?>
  </div>
</article>