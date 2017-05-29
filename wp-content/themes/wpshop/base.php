<?php

use Roots\Sage\Setup;
use Roots\Sage\Wrapper;

?>

<!doctype html>
<html <?php language_attributes(); ?>>

  <?php

  if (is_front_page()) {
    $stuff = "background-image: url('" . get_template_directory_uri() . "/assets/prod/imgs/bg-stuff.png')";
  } else {
    $stuff = '';
  }


  ?>

  <?php get_template_part('templates/head'); ?>
  <body <?php body_class('l-col'); ?> style="<?php echo $stuff; ?>">

    <!--[if IE]>
      <div class="alert alert-warning">
        <?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'sage'); ?>
      </div>
    <![endif]-->

    <?php
      do_action('get_header');
      get_template_part('templates/header');
    ?>

    <div class="wrap container l-fill l-row <?php echo is_page_template('template-narrow.php') ? 'l-contain-narrow' : 'l-contain'; ?> <?php echo isRegisteredAndPurchasing() ? 'is-registered-and-purchasing' : ''; ?>" role="document">

      <?php if (is_page('docs') || get_post_type( get_the_ID() ) === 'docs' ) : ?>
        <aside class="sidebar">
          <?php get_template_part('templates/sidebar', 'docs'); ?>
        </aside>
      <?php endif; ?>




      <main class="main l-col l-col-center l-fill">

        <!-- <div class="msg msg-success animated fadeInDown"><i class="fa fa-check-circle-o" aria-hidden="true"></i> Curabitur ullamcorper ultricies nisi. Donec vitae orci sed dolor rutrum auctor.</div>

        <div class="msg msg-notice animated fadeInDown"><i class="fa fa-info-circle" aria-hidden="true"></i> Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero.</div>

        <div class="msg msg-error animated fadeInDown"><i class="fa fa-times-circle-o" aria-hidden="true"></i> Non adipiscing dolor urna a orci. Mauris sollicitudin fermentum libero. Proin faucibus arcu quis ante.</div> -->



        <?php include Wrapper\template_path(); ?>

        <?php if(is_page('docs')) {
          get_template_part('templates/docs');
        } ?>

        <?php get_template_part('templates/components'); ?>
      </main>

    </div>

    <?php
      do_action('get_footer');

      get_template_part('templates/footer');

      wp_footer();
    ?>

  </body>
</html>
