<?php 

get_header();

get_template_part('components/header');

global $post;

$download = new EDD_Download($post->ID);
$prices = $download->get_prices();
$notes = $download->get_notes();
$id = get_the_ID();

$license_ver = edd_software_licensing()->get_download_version($download->ID);

?>

<main>

<header class="l-contain">
   <div class="content l-contain-s l-text-center">
      <h1><?php the_title(); ?></h1>
      <p><?= get_the_excerpt(); ?></p>
   </div>
</header>

<?php while (have_posts()) : the_post(); ?>

   <article <?php post_class('extension-single'); ?>>

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

               <div class="card card-dark">
               
                  <div class="extension-purchase-inner">
                     <?php 
                     
                     if ($id === 203186) {
                        $link = 'https://www.wpbeaverbuilder.com/?fla=4036';

                     } else if ($id === 233990) {
                        $link = 'https://www.yotpo.com/platform/reviews/';
                        
                     } else if ($id === 209061) {
                        $link = 'https://elementor.com/?ref=17221';

                     } else if ($id === 229781) {
                        $link = 'https://rechargepayments.com/';

                     } else {
                        $link = '#!';
                     }
                     
                     ?>

                     <div class="extension-image">
                        <img src="<?= get_the_post_thumbnail_url($id, 'large'); ?>" alt="<?= the_title(); ?>" />
                     </div>

                     <div class="extension-purchase-bottom <?= empty($prices) ? 'single-price' : ''; ?>">

                        <div class="extension-prices">
                           
                           <?php foreach ($prices as $price) { ?>
                              <div class="l-row extension-button" data-extension-id="<?= $download->ID; ?>" data-extension-index="<?= $price['index']; ?>">
                                 <p class="extension-selection-icon <?= $price['index'] === '1' ? 'is-selected' : ''; ?>"></p>
                                 <p class="extension-tier" itemprop="description"><?= $price['name']; ?> &mdash;</p>
                                 <p class="extension-price">
                                    <span itemprop="price">$<?= $price['amount']; ?></span> 
                                    <span class="extension-interval">/<?= $price['period']; ?></span>
                                 </p>
                              </div>
                           <?php } ?>

                        </div>

                        <div class="extension-add-to-cart">
                           
                        <?php if ($download->price == false) { ?>

                           <a href="/plugin-extensions/webhooks/releases/1.1.1/shopwp-webhooks.zip" class="btn btn-l" itemprop="url">Download</a>

                        <?php } else { ?>
                        
                           <a href="/checkout?edd_action=add_to_cart&download_id=<?= $id; ?>&edd_options[price_id]=1" class="btn btn-l" itemprop="url">Purchase<?= empty($prices) ? ' for $' . $download->price . '<small>/year</small>' : ''; ?></a>

                        <?php } ?>                        
                        
                        </div>

                     <div class="extension-terms">
                        <p>Every extension requires <a href="/purchase">ShopWP Pro</a>. All extensions are billed yearly. Cancel your subscription at any time before renewal.</p>

                        <p><?php if ($id === 232786) { ?>
                           Requires ShopWP Pro 7.0+ 
                        <?php } else { ?> 
                           Requires an <a href="https://shopify.pxf.io/5bPL0L" target="_blank" rel="noreferrer">active Shopify store</a>. 
                        <?php } ?></p>
                     </div>

                     <?php if (!empty($notes)) { ?>

                        <div class="extension-notes">
                           <span class="extension-version">Current version: <?= $license_ver; ?></span>
                           <?= $notes; ?>
                        </div>
                     <?php } ?>

                  </div>
                                 
               </div>

               <div class="shape"></div>

            </div>

         </div>
         
      </div>

   </article>

<?php endwhile; ?>

</main>

<script>
   jQuery('.extension-button').on('click', function() {
      
      var extensionId = jQuery(this).data('extension-id');
      var extensionIndex = jQuery(this).data('extension-index');

      jQuery('.extension-selection-icon').removeClass('is-selected');
      jQuery(this).find('.extension-selection-icon').addClass('is-selected');
      
      jQuery('.extension-add-to-cart .btn').attr('href', '/checkout?edd_action=add_to_cart&download_id=' + extensionId + '&edd_options[price_id]=' + extensionIndex)

   });
</script>

<?php 

get_footer();

?>