<div class="meta-wrapper l-row l-row-left">
  
   <?php 

   $user = wp_get_current_user();
   $url = esc_url( get_avatar_url( $user->ID, ['size' => '150'] ) );

   ?>


   <p class="byline author vcard">

      <a href="<?= esc_url(home_url('/purchase')); ?>" rel="author" class="fn post-logo-link" target="_blank">
         <img src="<?= $url; ?>" alt="WP Shopify" class="logo-header" />
      </a>

      <?php if ( $wp_query->current_post == 0 && !is_paged() ) { ?>
      <div class="author-info-wrap">
         <span class="author-name"><?= get_the_author_meta( 'first_name', $user->ID ) ?> <?= get_the_author_meta( 'last_name', $user->ID ) ?></span>
         <span class="author-bio-short"><?= get_the_author_meta('description', $user->ID); ?></span>
      </div>
      <?php } ?>
      
   </p>
  
</div>