<div class="meta-wrapper l-row l-row-left">
  
   <?php 

   $user_id = get_the_author_meta('ID');

   ?>

   <p class="byline author vcard">

      <img src="/wp-content/uploads/2022/01/me-3-maybe.jpg" alt="Andrew Robbins, creator of ShopWP" class="logo-header" width="50" height="50" />

      <?php if ( $wp_query->current_post == 0 && !is_paged() ) { ?>
      <div class="author-info-wrap">
         <span class="author-name">
            <a href="https://twitter.com/wpshopify" target="_blank" rel="noreferrer">
               <?= get_the_author_meta( 'first_name', $user_id ) ?> <?= get_the_author_meta( 'last_name', $user_id ) ?>
            </a>
         </span>
         <span class="author-bio-short"><?= get_the_author_meta('description', $user_id); ?></span>
      </div>
      <?php } ?>
      
   </p>
  
</div>

