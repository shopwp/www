<footer class="footer">

  <div class="footer-wrapper">



  <?php if (has_nav_menu('footer_navigation')) : ?>
    <nav class="nav-footer l-row l-row-center l-fill l-contain">
      <?php wp_nav_menu(['theme_location' => 'footer_navigation', 'menu_class' => 'nav l-row l-row-center']); ?>
    </nav>
  <?php endif; ?>

  <section class="footer-social-links">
    <a href="https://github.com/arobbins/wp-shopify" class="footer-social-link">
      <i class="fa fa-github"></i>
    </a>
    <a href="https://twitter.com/andrewmrobbins" class="footer-social-link">
      <i class="fa fa-twitter"></i>
    </a>
    <!-- <a href="https://instagram.com/simpleblend" class="footer-social-link">
      <i class="fa fa-instagram"></i>
    </a> -->
  </section>
  <section class="footer-attr">&copy; <?php echo date("Y") ?> WP Shopify </section>

</div>

</footer>
