<article <?php post_class(); ?>>

  <header>
     <a href="/faq">< Back to FAQ</a>
    <h2 class="entry-title"><?php the_title(); ?></h2>
  </header>

  <div class="entry-summary">
    <?php the_field('faq_answer', get_the_ID()); ?>
  </div>
</article>