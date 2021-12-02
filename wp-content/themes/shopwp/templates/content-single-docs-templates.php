<!--

Info table

-->
<section class="doc-template-info">

  <table class="template-table">

    <tbody>

      <?php if (have_rows('template_info', $post->ID)): ?>

        <?php while ( have_rows('template_info', $post->ID) ) : the_row(); ?>

          <tr>

            <td class="doc-info-key">
              <b><?php the_sub_field('key'); ?></b>
            </td>

            <td class="doc-info-value <?php echo get_sub_field('has_code') ? 'copy-trigger' : ''; ?>" data-clipboard-text='<?php the_sub_field('value'); ?>'>

              <?php if (get_sub_field('has_code')) { ?>

                <span class="code-snippet-inline">
                  <?= htmlspecialchars(get_sub_field('value')); ?>
                </span>

                <i class="fal fa-copy"></i>

              <?php } else { ?>
                <?php the_sub_field('value'); ?>
              <?php } ?>

            </td>

          </tr>

        <?php endwhile; ?>

      <?php endif; ?>

    </tbody>

  </table>

</section>


<!--

Data table

-->
<?php if (get_field('has_data_variable', $post->ID)) { ?>

  <section class="doc-template-data">

    <br><h2 class="doc-heading doc-sub-heading">Data</h2>
    <p>Many ShopWP templates require data to populate dynamic values such as a price or title. These values are stored inside a <span class="code-inline">$data</span> variable that can be accessed from within the template. The <span class="code-inline">$data</span> variable will be a PHP Object containing the below properties:</p>

    <table class="template-table">

      <thead>
        <tr>
          <th>Data Property</th>
          <th>Type</th>
          <th>Description</th>
        </tr>
      </thead>

      <tbody>

      <?php if (have_rows('template_data', $post->ID)): ?>

        <?php while ( have_rows('template_data', $post->ID) ) : the_row(); ?>

          <tr>
            <td class="template-data-property">
              <span class="code-snippet-inline">
                <?php the_sub_field('variable', $post->ID); ?>
              </span>
            </td>
            <td class="template-data-type"><?php the_sub_field('type', $post->ID); ?></td>
            <td class="template-data-description"><?php the_sub_field('description', $post->ID); ?></td>
          </tr>

        <?php endwhile; ?>

      <?php endif; ?>

      </tbody>
    </table>

  </section>

<?php } ?>


<!--

Default Template

-->
<?php if (get_field('template_example', $post->ID)) { ?>

  <section class="doc-template-examples">

    <h2 class="doc-heading doc-sub-heading">Default template:</h2>
    <pre class="code-snippet">
      <div class="loader"><?php include(locate_template('components/loader/loader-cog.php')); ?></div>
      <code data-language="php"> <?= htmlspecialchars(get_field('template_example', $post->ID)); ?> </code>
    </pre>
  </section>

<?php } ?>
