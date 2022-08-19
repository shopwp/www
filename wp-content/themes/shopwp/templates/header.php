  <header class="header">

    <div class="header-content l-row l-row-justify l-contain-wide">

      <a class="logo-link l-col l-col-left l-row-center" href="<?= esc_url(home_url('/')); ?>" aria-label="ShopWP logo link" style="width:140px;">
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 731.9 189.4" xml:space="preserve"><circle cx="91.2" cy="95.2" r="89.2" fill="#415aff"/><path fill="white" d="M77.5 54.4c2.7-6.8 8.7-11.5 15.4-11.5 6.8 0 12.7 4.7 15.4 11.5h7.2c-3-11.4-12-19.6-22.5-19.6-10.6 0-19.5 8.2-22.7 19.5h7.2zM138.2 65.5H45.9c-2 21.5-3.6 43.1-4.9 64.6.3 2.7 2.1 7.4 9.9 8H133.1c7.7-.6 9.6-5.3 9.9-8-1.2-21.6-2.8-43.1-4.8-64.6zm-18.5 38.2c-2.1 6.6-6 12.2-11.2 15.9-4.8 3.5-10.6 5.4-16.5 5.4h-.2c-5.8-.1-11.4-2-16.1-5.4-5.3-3.8-9.1-9.3-11.2-15.9l-.6-2h12l.3.7c2.8 6.9 9 11.3 16 11.3h.3c6.9-.1 13-4.4 15.7-11.3l.3-.7h12l-.8 2z"/><path d="M249 130.6c-10 0-21.1-3.3-30.6-10.8l8.6-13.3c7.7 5.6 15.8 8.5 22.5 8.5 5.9 0 8.5-2.1 8.5-5.3v-.3c0-4.4-6.9-5.9-14.8-8.2-10-2.9-21.3-7.6-21.3-21.4v-.3c0-14.5 11.7-22.6 26.1-22.6 9 0 18.9 3.1 26.6 8.2l-7.7 14c-7-4.1-14.1-6.6-19.3-6.6-4.9 0-7.4 2.1-7.4 4.9v.3c0 4 6.8 5.9 14.5 8.5 10 3.3 21.5 8.1 21.5 21.1v.3c0 15.8-11.8 23-27.2 23zM335.8 129.3V89.5c0-9.6-4.5-14.5-12.2-14.5S311 79.9 311 89.5v39.8h-20.2V32.2H311v35.9c4.7-6 10.6-11.4 20.9-11.4 15.3 0 24.2 10.1 24.2 26.5v46.1h-20.3zM408.6 130.9c-22.1 0-38.4-16.4-38.4-36.8v-.3c0-20.5 16.5-37.1 38.7-37.1 22.1 0 38.4 16.4 38.4 36.8v.3c0 20.5-16.5 37.1-38.7 37.1zm18.7-37.1c0-10.5-7.6-19.7-18.8-19.7-11.6 0-18.5 8.9-18.5 19.4v.3c0 10.5 7.6 19.7 18.8 19.7 11.6 0 18.5-8.9 18.5-19.4v-.3zM504.3 130.6c-10.8 0-17.4-4.9-22.2-10.6v30.6h-20.2V58h20.2v10.2c4.9-6.6 11.7-11.6 22.2-11.6 16.6 0 32.4 13 32.4 36.8v.3c.1 23.9-15.5 36.9-32.4 36.9zm12.3-37.1c0-11.8-8-19.7-17.4-19.7-9.4 0-17.3 7.8-17.3 19.7v.3c0 11.8 7.8 19.7 17.3 19.7 9.4 0 17.4-7.7 17.4-19.7v-.3zM623.5 129.8h-5.6l-20.2-58.1-20.3 58.1h-5.6l-24.9-68.4h7.3l20.5 59.6 20.5-59.8h5.2l20.5 59.8 20.5-59.6h7l-24.9 68.4zM695.6 130.9c-13.6 0-22.3-7.7-27.9-16.2v35.9h-6.5V61.5h6.5v15.2c5.9-8.9 14.5-16.8 27.9-16.8 16.4 0 33 13.2 33 35.2v.3c0 22-16.7 35.5-33 35.5zm25.9-35.5c0-17.8-12.4-29.3-26.6-29.3-14.1 0-27.7 11.8-27.7 29.1v.3c0 17.4 13.6 29.1 27.7 29.1 14.8 0 26.6-10.8 26.6-29v-.2z"/></svg>
      </a>

      <?php if (is_page('checkout')) { ?>

        <?php if (has_nav_menu('checkout_navigation')) : ?>
          <nav class="nav-primary l-row l-row-right l-fill l-col-center">
            <?php wp_nav_menu(['theme_location' => 'checkout_navigation', 'menu_class' => 'nav l-row']); ?>
          </nav>
        <?php endif; ?>

      <?php } else { ?>

        <?php if (has_nav_menu('primary_navigation')) : ?>
          <nav class="nav-primary l-row l-row-center l-fill l-col-center">
            <?php wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav l-row']); ?>
          </nav>
        <?php endif; ?>

        <?php if (has_nav_menu('primary_sub')) : ?>
         <!-- <div class="sub-nav-wrapper">
          <nav class="nav-primary-sub l-row l-row-right l-fill l-col-center">
            <?php wp_nav_menu(['theme_location' => 'primary_sub', 'menu_class' => 'nav l-row']); ?>
          </nav>
          <i class="fal fa-ellipsis-v"></i>
          </div> -->
        <?php endif; ?>

      <?php } ?>

      <?php if( !is_page('checkout') ) { ?>

        <div class="header-account l-row l-row-right l-col-center">

          <?php if (is_user_logged_in()) {  ?>
            <a href="/account" class="btn btn-account btn-secondary btn-s">My Account</a>
            <a href="<?= wp_logout_url('/login'); ?>" class="menu-item-manual">Logout</a>
          <?php } else { ?>
            <a href="/pricing" class="btn btn-s btn-secondary">View pricing</a>
            <a href="/login" class="menu-item-manual">Login</a>
          <?php } ?>

        </div>

      <?php } ?>

      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgs/icon-mobile.svg" class="icon-mobile-open" alt="Mobile ShopWP Menu Icon" />

    </div>

  </header>

<div class="mobile-menu-wrapper">
   <?php wp_nav_menu(['theme_location' => 'mobile_navigation', 'menu_class' => 'nav-mobile l-row']); ?>
   <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/imgs/icon-mobile-close.svg" class="icon-mobile icon-mobile-close" alt="Mobile ShopWP Menu Close Icon" />
</div>