<?php 

if (!is_page('checkout')) {
   include(locate_template('components/getting-started-inline/view.php'));
}

?>

<footer class="footer">
  <div class="footer-wrapper">

   <nav class="nav-footer l-row l-row-center l-fill l-contain">
      <?php if (has_nav_menu('footer_1')) : ?>
         <div class="footer-col">
            <h3>The Plugin</h3>
            <?php wp_nav_menu(['theme_location' => 'footer_1', 'menu_class' => 'nav']); ?>
         </div>
      <?php endif; ?>

      <?php if (has_nav_menu('footer_2')) : ?>
         <div class="footer-col">
            <h3>Company</h3>      
            <?php wp_nav_menu(['theme_location' => 'footer_2', 'menu_class' => 'nav']); ?>
         </div>
      <?php endif; ?>
         
      <?php if (has_nav_menu('footer_3')) : ?>
         <div class="footer-col">
            <h3>Support</h3>
            <?php wp_nav_menu(['theme_location' => 'footer_3', 'menu_class' => 'nav']); ?>
         </div>
      <?php endif; ?>

      <?php if (has_nav_menu('footer_4')) : ?>
         <div class="footer-col">
            <h3>Account</h3>      
            <?php wp_nav_menu(['theme_location' => 'footer_4', 'menu_class' => 'nav']); ?>
         </div>
      <?php endif; ?>

      <?php if (has_nav_menu('footer_5')) : ?>
         <div class="footer-col">
            <h3>Contact</h3>      
            <?php wp_nav_menu(['theme_location' => 'footer_5', 'menu_class' => 'nav']); ?>

            <section class="footer-social-links">

               <a href="https://www.youtube.com/c/WPShopify" class="footer-social-link" target="_blank">
                  <i class="fab fa-youtube"></i>
               </a>

               <a href="https://twitter.com/wpshopify" class="footer-social-link" target="_blank">
                  <i class="fab fa-twitter"></i>
               </a>

               <a href="https://github.com/wpshopify" class="footer-social-link" target="_blank">
                  <i class="fab fa-github"></i>
               </a>

            </section>

         </div>
      <?php endif; ?>
   </nav>

      <section class="footer-attr">
         <a class="logo-link" href="<?= esc_url(home_url('/')); ?>">
            <img src="/wp-content/themes/wpshop/assets/imgs/logo-mark-v2.svg" alt="WP Shopify" class="logo-header">
            </a>

            <small>&copy; <?php echo date("Y"); ?> WP Shopfiy</small>
      </section>

   </div>


</footer>
