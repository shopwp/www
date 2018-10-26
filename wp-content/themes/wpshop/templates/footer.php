<footer class="footer">

  <div class="footer-wrapper">

  <?php if (has_nav_menu('footer_navigation')) : ?>
    <nav class="nav-footer l-row l-row-center l-fill l-contain">
      <?php wp_nav_menu(['theme_location' => 'footer_navigation', 'menu_class' => 'nav l-row l-row-center']); ?>
    </nav>
  <?php endif; ?>

  <section class="footer-social-links">

    <a href="https://wpshop.io/purchase" class="footer-social-link gtm-link-support" target="_blank">
      <i class="fab fa-slack" aria-hidden="true"></i>
    </a>

    <a href="https://github.com/wpshopify" class="footer-social-link" target="_blank">
      <i class="fab fa-github"></i>
    </a>

    <a href="https://twitter.com/wpshopify" class="footer-social-link" target="_blank">
      <i class="fab fa-twitter"></i>
    </a>

    <!-- <a href="https://instagram.com/simpleblend" class="footer-social-link">
      <i class="fa fa-instagram"></i>
    </a> -->

  </section>
  <section class="footer-attr">&copy; <?php echo date("Y") ?> WP Shopify </section>

</div>

</footer>
