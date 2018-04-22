<div class="changelogs">

  <header class="changelog-types">
    <p class="changelog-type code-inline  is-active" data-changelog-type="free">Free version</p>
    <p class="changelog-type code-inline" data-changelog-type="pro">Pro version</p>
  </header>

  <h4 class="changelog-type-heading"><span>Free</span> version</h4>
  <section class="changelog-wrapper is-active" data-changelog-type="free">
    <?= the_field('changelog_free', $post->ID); ?>
  </section>

  <section class="changelog-wrapper" data-changelog-type="pro">
    <?= the_field('changelog_pro', $post->ID); ?>
  </section>

</div>
