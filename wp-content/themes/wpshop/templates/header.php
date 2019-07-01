<?php if ( $post->post_type !== 'docs') { ?>

  <header class="header">

    <div class="header-content l-row l-row-justify l-contain">

      <a class="logo-link" href="<?= esc_url(home_url('/')); ?>">
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

          <!-- <a href="https://wpshop.io/purchase" class="header-social-link gtm-link-support" target="_blank">
            <i class="fab fa-slack gtm-link-support" aria-hidden="true"></i>
          </a> -->

          <!-- <a href="https://github.com/arobbins/wp-shopify" class="header-social-link" target="_blank">
            <i class="fa fa-github"></i>
          </a>

          <a href="https://twitter.com/andrewmrobbins" class="header-social-link" target="_blank">
            <i class="fa fa-twitter"></i>
          </a> -->

          <?php if(is_user_logged_in()) { ?>
            <a href="/account" class="btn btn-account">My Account</a>
            <a href="<?php echo wp_logout_url('/login'); ?>" class="link-account">Log Out</a>

          <?php } else { ?>
            <a href="/purchase" class="btn btn-account">Purchase</a>
            <a href="/login" class="menu-item-manual">Log In</a>
          <?php } ?>

          <?php if(edd_get_cart_quantity() > 0) { ?>
            <a href="<?php echo edd_get_checkout_uri(); ?>" class="header-cart-link">
              <i class="far fa-shopping-cart"></i> <span class="header-cart-quantity"><?php echo edd_get_cart_quantity(); ?></span>
            </a>
          <?php } ?>

          <?php // echo do_shortcode('[wps_cart]'); ?>

        </div>

      <?php } ?>

      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgs/icon-mobile.svg?v=2" class="icon-mobile-open" alt="Mobile WP Shopify Menu Icon" />


      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgs/icon-mobile-close.svg?v=2" class="icon-mobile icon-mobile-close" alt="Mobile WP Shopify Menu Close Icon" />

    </div>


    <?php if (has_nav_menu('mobile_navigation')) : ?>
      <nav class="nav-mobile l-row l-row-right l-fill l-col-center animated">
        <?php wp_nav_menu(['theme_location' => 'mobile_navigation', 'menu_class' => 'nav l-row']); ?>
      </nav>
    <?php endif; ?>

  </header>

<?php } ?>
