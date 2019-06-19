<section class="component component-screenshots l-col l-col-center <?= $has_bg ? 'has-background' : ''; ?>">

  <?php if ($short_desc) { ?>
    <div class="screenshots-short-description">
      <?= $short_desc; ?>
    </div>
  <?php } ?>

  <ul class="screenshots">

    <?php

    if( have_rows('screenshots') ):

      while ( have_rows('screenshots') ) : the_row();

      $image = get_sub_field('screenshot_image');

      ?>

        <li class="screenshot l-row l-row-left">

          <div class="screenshot-image-wrapper  <?= $has_bg ? 'edge--bottom edge--top--reverse' : ''; ?>">
            <img src="<?= $image['url']; ?>" alt="<?= $image['alt']; ?>" class="screenshot-image">
          </div>

          <div class="screenshot-image-description">
            <h2 class="screenshot-heading"><?php the_sub_field('sreenshot_heading'); ?></h2>
            <?php the_sub_field('screenshot_description'); ?>
          </div>

        </li>

      <?php endwhile;

    endif;

    ?>

  </ul>

</section>
