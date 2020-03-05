<footer class="footer">

  <div class="footer-wrapper">

  <?php if (has_nav_menu('footer_navigation')) : ?>

    <nav class="nav-footer l-row l-row-center l-fill l-contain">
      <?php wp_nav_menu(['theme_location' => 'footer_navigation', 'menu_class' => 'nav l-row l-row-center']); ?>
    </nav>

  <?php endif; ?>

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

    <a href="https://www.instagram.com/wpshopifyplugin/" class="footer-social-link gtm-link-support" target="_blank">
      <i class="fab fa-instagram" aria-hidden="true"></i>
    </a>

  </section>
  <section class="footer-attr">
     <a class="logo-link" href="<?= esc_url(home_url('/purchase')); ?>">
        <img src="<?php the_field('theme_logo_mark', 'option'); ?>" alt="WP Shopify" class="logo-header">
      </a>
  </section>

</div>


</footer>
