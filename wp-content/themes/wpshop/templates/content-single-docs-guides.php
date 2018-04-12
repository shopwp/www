<!--

Guide Content

-->
<?php if (get_field('guide_content', $post->ID)) { ?>

  <section class="guide-content">
    <?php the_field('guide_content', $post->ID); ?>
  </section>

<?php } ?>


<!--

Guide Steps

-->
<?php if (have_rows('guide_steps', $post->ID)): ?>

  <section class="steps-wrapper">

    <?php while ( have_rows('guide_steps', $post->ID) ) : the_row(); ?>

      <?php if (get_sub_field('step_title')) { ?>

        <!--

        Step Heading

        -->
        <h2 class="step-heading step-sub-heading doc-heading-secondary">
          <?php the_sub_field('step_title'); ?>
        </h2>


        <!--

        Step Description

        -->
        <div class="step-description">
          <?php the_sub_field('step_content'); ?>
        </div>


        <!--

        Step Example

        -->
        <pre class="step-example code-snippet">
          <div class="loader"><?php include(locate_template('components/loader/loader-cog.php')); ?></div>
          <code data-language="php"> <?php the_sub_field('step_example'); ?> </code>
        </pre>


      <?php } ?>

    <?php endwhile; ?>

  </section>

<?php endif; ?>
