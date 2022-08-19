<section class="component component-feature-highlights">

  <div class="l-contain l-row l-row-justify" style="padding-left: 75px;">

  <div class="l-box-2" style="padding-top: 60px;padding-right:0;">
    <h2>Buy buttons? No problem 👍</h2><br>
    <p>Display product variants as <b data-feature-demo="1">dropdowns</b> or <b data-feature-demo="2">buttons</b>. Enable one-time purchase or <b data-feature-demo="3">subscriptions</b>. You can send customers directly to the <b data-feature-demo="4">Shopify checkout page</b>, or add products to the <b data-feature-demo="5">built-in cart</b> experience.</p>
    <p>We have all the shortcodes and blocks you need to build a uniquely branded shopping experience on WordPress.</p>
  </div>

  <div class="l-box-2" style="min-height: 437px;">
<div class="feature-demos">
        <div class="feature-demo" data-feature-demo="1">
            <?= do_shortcode('[wps_products_buy_button title="Super*" limit="1"]'); ?>
        </div>

        <div class="feature-demo active" data-feature-demo="2">
            <?= do_shortcode('[wps_products_buy_button title="Super*" limit="1" variant_style="buttons"]'); ?>
        </div>

        <div class="feature-demo" data-feature-demo="3">
            <?= do_shortcode('[wps_products_buy_button subscriptions="true" title="Super awesome sunglasses" limit="1" variant_style="buttons"]'); ?>
        </div>

        <div class="feature-demo" data-feature-demo="4">
            <?= do_shortcode('[wps_products_buy_button title="Super awesome sunglasses" limit="1" variant_style="buttons" direct_checkout="true" link_target="_blank"]'); ?>
        </div>

    </div>
    </div>
    
  </div>
    <script>
        jQuery('.component-feature-highlights p b').on('click', function() {

            var feat = jQuery(this).data('feature-demo');

            if (feat === 5) {
                return;
            }

            jQuery('.feature-demo').removeClass('active');
            jQuery('.feature-demo[data-feature-demo="' + feat + '"]').addClass('active')
        })

        jQuery('.component-feature-highlights [data-feature-demo="5"]').on('click', function() {
            wp.hooks.doAction('do.cartToggle', 'open');
        })

         
    </script>
</section>