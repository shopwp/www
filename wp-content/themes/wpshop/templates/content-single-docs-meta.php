<h1 class="doc-meta-title"><?= $post->post_title; ?></h1>

<div class="doc-meta-wrapper l-row">

  <?php if ($type) { ?>
    <p class="doc-meta-item doc-meta-item-first">
      <span class="doc-type doc-type-<?= rtrim($type[0]->slug, "s"); ?>"><?= rtrim($type[0]->slug, "s"); ?></span>
    </p>
  <?php } ?>

  <?php if ($since && $type[0]->slug !== 'getting-started') { ?>
    <p class="doc-meta-item doc-since <?= !$source ? 'doc-meta-item-last' : ''; ?>">Since: v<?= $since; ?></p>

  <?php } else { ?>
    <p class="doc-meta-item doc-since doc-meta-item-last <?= !$source ? 'doc-meta-item-last' : ''; ?>">Updated on: <?= date("F d, Y", strtotime($post->post_modified)); ?></p>

  <?php } ?>


  <?php if ($source && $type[0]->slug !== 'getting-started') { ?>
    <p class="doc-meta-item doc-source doc-meta-item-last"><a href="<?= $source; ?>" class="doc-source-link" target="_blank">Source: <i class="fas fa-external-link-square-alt"></i></a></p>
  <?php } ?>


</div>

<div class="doc-meta-description">
  <?php the_field('doc_description', $post->ID); ?>
</div>
