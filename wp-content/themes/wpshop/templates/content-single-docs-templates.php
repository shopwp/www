<section class="doc-template-info">

  <table class="template-table">

    <tbody>

      <?php if (have_rows('template_info', $post->ID)): ?>

        <?php while ( have_rows('template_info', $post->ID) ) : the_row(); ?>

          <tr>

            <td>
              <b><?php the_sub_field('key'); ?></b>
            </td>

            <td class="doc-shortcode-attribute-description">

              <?php if (get_sub_field('has_code')) { ?>
                <pre class="code-snippet-inline"><code class="markdown"><?php the_sub_field('value'); ?></code></pre>

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


<?php if (get_field('has_data_variable', $post->ID)) { ?>

<section class="doc-template-data">

  <br><h2 class="doc-heading doc-sub-heading">Data</h2>
  <p>Many templates have unique data that populate dynamic fields such as a price or title. This data can be accessed by using the unique <span class="code-inline">$data</span> variable from within the template. This variable is a PHP Object and will contain the below properties:</p>

  <table class="template-table">

    <thead>
      <tr>
        <th>Data Property</th>
        <th>Description</th>
      </tr>
    </thead>

    <tbody>

    <?php if (have_rows('template_data', $post->ID)): ?>

      <?php while ( have_rows('template_data', $post->ID) ) : the_row(); ?>

        <tr>
          <td><?php the_sub_field('variable', $post->ID); ?></td>
          <td><?php the_sub_field('description', $post->ID); ?></td>
        </tr>

      <?php endwhile; ?>

    <?php endif; ?>

    </tbody>
  </table>

</section>

<?php } ?>













<?php if (get_field('template_example', $post->ID)) { ?>

  <section class="doc-template-examples">

    <h2 class="doc-heading doc-sub-heading">Default template:</h2>
    <pre class="code-snippet">
      <div class="loader"><?php include(locate_template('components/loader/loader-cog.php')); ?></div>
      <code class="php"> <?php the_field('template_example', $post->ID); ?> </code>
    </pre>
  </section>

<?php } ?>
