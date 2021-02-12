<div class="meta-wrapper l-row l-row-left">
  
   <?php 

   $user_id = get_the_author_meta('ID');
   $url = esc_url( get_avatar_url( $user_id, ['size' => '250'] ) );

   ?>

   <p class="byline author vcard">

      <img src="<?= $url; ?>" alt="WP Shopify" class="logo-header" />

      <?php if ( $wp_query->current_post == 0 && !is_paged() ) { ?>
      <div class="author-info-wrap">
         <span class="author-name">
            <a href="https://twitter.com/wpshopify" target="_blank">
               <?= get_the_author_meta( 'first_name', $user_id ) ?> <?= get_the_author_meta( 'last_name', $user_id ) ?>
            </a>
         </span>
         <span class="author-bio-short"><?= get_the_author_meta('description', $user_id); ?></span>
      </div>
      <?php } ?>
      
   </p>
  
</div>

