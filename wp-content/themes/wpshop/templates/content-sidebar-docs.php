<?php

/*

Constructs the category data store

*/
function construct_category_struct() {

  $cats = get_categories();
  $catsStructured = [];

  foreach ($cats as $cat) {
    $catsStructured[$cat->category_nicename] = [];
  }

  return $catsStructured;

}


/*

Gets the doc nice_name

*/
function get_doc_name($templateCat) {
  return $templateCat[0]->category_nicename;
}


?>

<div class="sidebar-inner docs">

  <div class="l-row">
    <a href="/" class="logo-wrapper">
      <img src="<?php the_field('theme_logo_docs', 'option'); ?>" alt="WP Shopify Documentation" class="logo-header">
    </a>
    <div class="docs-version-wrapper">
      <p class="docs-version"><i class="fal fa-cog fa-spin"></i></p>
    </div>
  </div>

  <nav class="docs-nav">

    <div class="l-row l-row-left">

      <a href="https://travis-ci.com/arobbins/wp-shopify-pro" class="docs-travis-build" target="_blank">
        <img src="https://travis-ci.com/arobbins/wp-shopify-pro.svg?token=FmC2p6cxqRrxLpZfViYm&branch=master" alt="WP Shopify Travis CI Build Status">
      </a>

      <a href="https://github.com/wpshopify/wp-shopify" class="docs-social-link" target="_blank">
        <i class="fab fa-github"></i>
      </a>

      <a href="https://join.slack.com/wpshopify/shared_invite/MTg5OTQxODEwOTM1LTE0OTU5ODY2MTktN2Y1ODk0YzBlNg" class="docs-social-link gtm-link-support" target="_blank">
        <i class="fab fa-slack" aria-hidden="true"></i>
      </a>

    </div>

  </nav>

  <ul class="doc-cats">

    <?php

    $currentID = get_the_id();
    $terms = get_taxonomy_hierarchy('types');
    $stuff = construct_category_struct();


    /*

    For each "types" taxonomy

    Getting started
    Common Issues
    Guides
    Shortcodes
    Templates
    Actions
    Filters
    Conditional Tags

    */

    foreach ($terms as $term) {

      // Get all the posts that belong to Templates, Actions, etc
      $docs = get_posts([
        'posts_per_page' => -1,
        'post_type' => 'docs',
        'tax_query' => [[
          'taxonomy' => 'types',
          'field' => 'term_id',
          'terms' => $term->term_id
        ]]
      ]);

    ?>

    <?php if ($term->slug !== 'templates') { ?>
      <li class="doc-cat doc-collapsable-trigger"><i class="fal fa-minus-circle"></i> <?= $term->name; ?></li>

    <?php } else { ?>
      <li class="doc-cat"><?= $term->name; ?></li>

    <?php } ?>


    <?php if ($term->slug !== 'templates') { ?>

      <ul class="doc-terms doc-collapsable">

        <?php

        /*

        For each "doc" post

        */

        foreach ($docs as $doc) {

          $title = $doc->post_title; ?>

          <li class="doc-term <?= $currentID === $doc->ID ? 'is-current-doc' : ''; ?>" data-doc-id="<?= $doc->ID; ?>">
            <a href="<?= get_permalink($doc->ID); ?>" class="doc-title"><?= $title; ?></a>
            <span class="doc-type doc-type-<?= rtrim($term->slug, "s"); ?>"><?= rtrim($term->slug, "s"); ?> <i class="fal fa-cog fa-spin"></i></span>
          </li>

        <?php } ?>

      </ul>

    <?php } else {

      foreach ($docs as $doc) {

        $templateCat = get_the_category($doc->ID);
        $templateCatName = get_doc_name($templateCat);

        if (!empty($templateCat)) {
          $stuff[$templateCatName][] = $doc;
        }

      }

    }



    if ($term->slug === 'templates') {

      foreach ($stuff as $key => $category) { ?>

        <li class="doc-sub-cat-group">
          <span class="doc-sub-cat doc-collapsable-trigger"><i class="fal fa-minus-circle"></i> <?= $key; ?></span>

          <ul class="doc-terms doc-terms-sub doc-collapsable">

            <?php foreach ($category as $key => $template) { ?>
              <li class="doc-term" data-doc-id="<?= $template->ID; ?>">
                <span class="doc-title"><?= $template->post_title; ?></span>
                <span class="doc-type doc-type-<?= rtrim($term->slug, "s"); ?>"><?= rtrim($term->slug, "s"); ?> <i class="fal fa-cog fa-spin"></i> </span>
              </li>
            <?php } ?>

          </ul>


        </li>

      <?php }

    }


  } ?>



  </ul>

</div>
