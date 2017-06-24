<?php if (!is_page('auth')) { ?>

  <header class="header">

    <div class="header-content l-row l-row-justify l-contain">

      <a class="logo-link" href="<?= esc_url(home_url('/')); ?>">

        <!-- <?php if( is_page('home') ) { ?>
          <img src="<?php the_field('theme_logo_mark', 'option'); ?>" alt="WP Shopify" class="logo-header">
        <?php } else { ?>
          <img src="<?php the_field('theme_logo_primary', 'option'); ?>" alt="WP Shopify" class="logo-header">
        <?php } ?> -->

        <img src="<?php the_field('theme_logo_primary', 'option'); ?>" alt="WP Shopify" class="logo-header">

      </a>

      <?php if( is_page('checkout') ) { ?>

        <?php if (has_nav_menu('checkout_navigation')) : ?>
          <nav class="nav-primary l-row l-row-right l-fill l-col-center">
            <?php wp_nav_menu(['theme_location' => 'checkout_navigation', 'menu_class' => 'nav l-row']); ?>
          </nav>
        <?php endif; ?>

      <?php } else { ?>

        <?php if (has_nav_menu('primary_navigation')) : ?>
          <nav class="nav-primary l-row l-row-right l-fill l-col-center">
            <?php wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav l-row']); ?>
          </nav>
        <?php endif; ?>

      <?php } ?>

      <?php if( !is_page('checkout') ) { ?>

        <div class="header-account l-row l-col-center">

          <a href="https://join.slack.com/wpshopify/shared_invite/MTg5OTQxODEwOTM1LTE0OTU5ODY2MTktN2Y1ODk0YzBlNg" class="header-social-link" target="_blank">
            <i class="fa fa-slack" aria-hidden="true"></i>
          </a>

          <a href="https://github.com/arobbins/wp-shopify" class="header-social-link" target="_blank">
            <i class="fa fa-github"></i>
          </a>

          <a href="https://twitter.com/andrewmrobbins" class="header-social-link" target="_blank">
            <i class="fa fa-twitter"></i>
          </a>

          <?php if(is_user_logged_in()) { ?>
            <a href="/account" class="btn btn-account btn-s">My Account</a>
            <a href="<?php echo wp_logout_url('/login'); ?>" class="link-account">Logout</a>

          <?php } else { ?>
            <a href="/login" class="btn btn-account btn-s">Login</a>

          <?php } ?>

          <?php if(edd_get_cart_quantity() > 0) { ?>
            <a href="<?php echo edd_get_checkout_uri(); ?>" class="header-cart-link">
              <i class="fa fa-shopping-cart" aria-hidden="true"></i> <span class="header-cart-quantity"><?php echo edd_get_cart_quantity(); ?></span>
            </a>
          <?php } ?>

          <?php // echo do_shortcode('[wps_cart]'); ?>

        </div>

      <?php } ?>

      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgs/icon-mobile.svg" class="icon-mobile" alt="Mobile WP Shopify Menu" />

    </div>


    <?php if (has_nav_menu('mobile_navigation')) : ?>
      <nav class="nav-mobile l-row l-row-right l-fill l-col-center">
        <?php wp_nav_menu(['theme_location' => 'mobile_navigation', 'menu_class' => 'nav l-row']); ?>
      </nav>
    <?php endif; ?>

  </header>

<?php } else { ?>

  <div class="l-contain l-col l-col-center l-row-center logo-header-auth-wrapper">
    <img src="<?php the_field('theme_logo_mark', 'option'); ?>" alt="WP Shopify" class="logo-header-auth">
    <h1>Authenticating</h1>
    <p class="auth-notifying">Please wait ...</p>
    <div class="spinner is-visible"></div>
  </div>

<?php } ?>
