<?php 

global $post;

$download = new EDD_Download($post->ID);
$prices = $download->get_prices();
$notes = $download->get_notes();
$id = get_the_ID();
$license_ver = edd_software_licensing()->get_download_version($download->ID);

while (have_posts()) : the_post(); ?>

   <article <?php post_class('extension-single'); ?>>

      <header class="extension-single-header">

         <div class="l-row">

            <h1 class="extension-name"><?php the_title(); ?></h1>
            <p class="extension-excerpt"><?= get_the_excerpt(); ?></p>
         </div>

      </header>

      <div class="entry-content l-contain">

         <div class="l-row">

            <div class="l-fill extension-content">
               <div class="extension-breadcrumbs">
                  <a href="/extensions">All Extensions</a>
                  <span class="extension-breadcrumbs-separator">
                     <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="play" class="svg-inline--fa fa-play fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"></path></svg>
                  </span>
                  <span class="extension-breadcrumbs-current"><?php the_title(); ?></span>
               </div>
               <div class="extension-content-inner">
               <?= $download->post_content; ?>
               </div>
            </div>

            <div class="l-box-3 extension-purchase" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
               <?php 
               
               if ($id === 203186) {
                  $link = 'https://www.wpbeaverbuilder.com/?fla=4036';

               } else {
                  $link = 'https://elementor.com/?ref=17221';
               }
               
               ?>
               <a class="post-thumb" href="<?= $link; ?>" target="_blank" style="background-image: url('<?= get_the_post_thumbnail_url($id, 'full'); ?>');"></a>

               <div class="extension-purchase-inner">

                  <div class="extension-prices">
                     
                     <?php foreach ($prices as $price) { ?>
                        <div class="l-row extension-button" data-extension-id="<?= $download->ID; ?>" data-extension-index="<?= $price['index']; ?>">
                           <p class="extension-selection-icon <?= $price['index'] === '1' ? 'is-selected' : ''; ?>"></p>
                           <p class="extension-tier" itemprop="description"><?= $price['name']; ?></p>
                           <p class="extension-price">
                              <span itemprop="price">$<?= $price['amount']; ?></span> 
                              <span class="extension-interval">/<?= $price['period']; ?></span>
                           </p>
                        </div>
                     <?php } ?>

                  </div>

                  <div class="extension-add-to-cart">
                     <a href="/checkout?edd_action=add_to_cart&download_id=<?= $id; ?>&edd_options[price_id]=1" class="btn btn-l" itemprop="url">Purchase</a>
                  </div>

                  <div class="extension-terms">
                     <p>Every extension requires <a href="/purchase">WP Shopify Pro</a>. All purchase options are billed yearly. You may cancel your subscription at any time.</p>
                  </div>

               </div>
               
               <?php if (!empty($notes)) { ?>
                  <p>Requires an <a href="https://www.shopify.com/?ref=wps" target="_blank">active Shopify store</a>.</p>
                  <div class="extension-notes">
                     <span class="extension-version">Current version: <?= $license_ver; ?></span>
                     <?= $notes; ?>
                  </div>
               <?php } ?>
               

            </div>
            

         </div>
         
      </div>

   </article>

<?php endwhile; ?>

<script>
   jQuery('.extension-button').on('click', function() {
      
      var extensionId = jQuery(this).data('extension-id');
      var extensionIndex = jQuery(this).data('extension-index');

      jQuery('.extension-selection-icon').removeClass('is-selected');
      jQuery(this).find('.extension-selection-icon').addClass('is-selected');
      
      jQuery('.extension-add-to-cart .btn').attr('href', '/checkout?edd_action=add_to_cart&download_id=' + extensionId + '&edd_options[price_id]=' + extensionIndex)

   });
</script>