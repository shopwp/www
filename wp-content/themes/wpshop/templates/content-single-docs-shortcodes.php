
<?php if (get_field('shortcode_example')) { ?>

  <section class="doc-shortcode-examples">

    <h2 class="doc-heading doc-sub-heading">Examples:</h2>
    <pre class="code-snippet">
      <div class="loader"><?php include(locate_template('components/loader/loader-cog.php')); ?></div>
      <code class="js"> <?php the_field('shortcode_example'); ?> </code>
    </pre>
  </section>

<?php } ?>



<?php if (have_rows('shortcode_attributes', $post->ID)): ?>

<section class="doc-shortcode-attributes">

  <h2 class="doc-heading doc-sub-heading">Avaialble attributes:</h2>

  <ul class="doc-shortcode-attributes-wrapper">

    <?php while ( have_rows('shortcode_attributes', $post->ID) ) : the_row(); ?>

      <?php $attributeNaem = get_sub_field('name'); ?>

      <li class="doc-shortcode-attribute">

      <div class="l-row">

        <h3 class="doc-shortcode-attribute-name">
          <i class="fal fa-code"></i> <?= $attributeNaem; ?>
        </h3>

        <?php if (get_sub_field('description')) { ?>
          <p class="doc-shortcode-attribute-description"><?php the_sub_field('description'); ?></p>
        <?php } ?>

      </div>


      <?php if (have_rows('value')): ?>

        <div class="doc-shortcode-attribute-values">

          <table class="doc-table">

            <thead>
              <tr>
                <th>Possible Values</th>
                <th>Example</th>
                <th>Default</th>
              </tr>
            </thead>

            <tbody>

        <?php while ( have_rows('value') ) : the_row(); ?>

          <tr>

            <?php if (get_sub_field('value')) { ?>

              <td class="doc-shortcode-attribute-name">

                <?php if (get_sub_field('is_real_value')) { ?>
                  <pre class="code-snippet-inline"><code class="markdown"><?php the_sub_field('value'); ?></code></pre>

                <?php } else { ?>
                  <?php the_sub_field('value'); ?>

                <?php } ?>

              </td>

            <?php } ?>



            <?php if (get_sub_field('description')) { ?>

              <td class="doc-shortcode-attribute-description">

              <?php if (get_sub_field('is_example')) { ?>
                <pre class="code-snippet-inline"><code class="markdown copy-trigger" data-clipboard-text='<?php the_sub_field('description') ?>'><?php the_sub_field('description'); ?></code></pre>

              <?php } else { ?>
                <?php the_sub_field('description'); ?>

              <?php } ?>

              </td>

            <?php } ?>





            <td class="doc-shortcode-attribute-name">

              <?php

              if (empty(get_sub_field('default_value'))) {
                $defaultValue = 'false';

              } else {
                $defaultValue = get_sub_field('default_value');
              }

              ?>

              <?= $defaultValue; ?>

            </td>




          </tr>

        <?php endwhile; ?>

          </tbody>

          </table>

        </div>

      <?php endif; ?>

      </li>

    <?php endwhile; ?>

  </ul>

</section>

<?php endif;
