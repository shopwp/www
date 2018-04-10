<h1 class="doc-meta-title"><?php the_title(); ?></h1>

<div class="doc-meta-wrapper l-row">

  <?php if ($type) { ?>
    <p class="doc-meta-item doc-meta-item-first">
      <span class="doc-type doc-type-<?= rtrim($type[0]->slug, "s"); ?>"><?= rtrim($type[0]->slug, "s"); ?></span>
    </p>
  <?php } ?>


  <?php if ($since) { ?>
    <p class="doc-meta-item doc-since">Version added: v<?= $since; ?></p>
  <?php } ?>


  <?php if ($source) { ?>
    <p class="doc-meta-item doc-source doc-meta-item-last"><a href="<?= $source; ?>" class="doc-source-link">Source: <i class="fas fa-external-link-square-alt"></i></a></p>
  <?php } ?>


</div>

<div class="doc-meta-description">
  <!-- <h2 class="doc-heading doc-sub-heading">Description:</h2> -->
  <?php the_field('shortcode_description', $post->ID); ?>
</div>
