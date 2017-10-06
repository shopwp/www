<footer class="footer">

  <div class="footer-wrapper">

  <?php if (has_nav_menu('footer_navigation')) : ?>
    <nav class="nav-footer l-row l-row-center l-fill l-contain">
      <?php wp_nav_menu(['theme_location' => 'footer_navigation', 'menu_class' => 'nav l-row l-row-center']); ?>
    </nav>
  <?php endif; ?>

  <section class="footer-social-links">

    <a href="https://join.slack.com/wpshopify/shared_invite/MTg5OTQxODEwOTM1LTE0OTU5ODY2MTktN2Y1ODk0YzBlNg" class="footer-social-link gtm-link-support" target="_blank">
      <i class="fa fa-slack gtm-link-support" aria-hidden="true"></i>
    </a>

    <a href="https://github.com/arobbins/wp-shopify" class="footer-social-link" target="_blank">
      <i class="fa fa-github"></i>
    </a>

    <a href="https://twitter.com/andrewmrobbins" class="footer-social-link" target="_blank">
      <i class="fa fa-twitter"></i>
    </a>

    <!-- <a href="https://instagram.com/simpleblend" class="footer-social-link">
      <i class="fa fa-instagram"></i>
    </a> -->

  </section>
  <section class="footer-attr">&copy; <?php echo date("Y") ?> WP Shopify </section>

</div>

</footer>
