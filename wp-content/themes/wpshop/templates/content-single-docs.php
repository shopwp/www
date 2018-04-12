<div class="doc-content-wrapper">

  <?php

  error_log('Initial load -- getting cache');

  $cache = get_transient('wpshopify_' . $post->ID);

  if ($cache) {

    error_log('Initial load -- cache found, just displaying');

    echo $cache['content'];

  } else {

    error_log('Initial load -- cache NOT found, populating ....');

    $type = get_field('doc_type', $post->ID);
    $since = get_field('doc_since', $post->ID);
    $source = get_field('doc_source', $post->ID);

    ob_start();
    include(locate_template('templates/content-single-docs-meta.php'));
    include(locate_template('templates/content-single-docs-' . $type[0]->slug . '.php'));
    $content = ob_get_contents();
    ob_end_clean();

    echo $content;

    error_log('Initial load -- cache NOT found, setting cache ....');

    set_transient('wpshopify_' . $post->ID, [
      'content' => $content,
      'slug'    => $post->post_name,
      'url'     => get_post_permalink($post->ID)
    ]);

  }

  ?>

</div>
