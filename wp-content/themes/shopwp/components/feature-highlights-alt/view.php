<section class="component component-feature-highlights-alt">

  <div class="l-contain l-row l-row-justify">

    <div class="l-box-2">
      <h2>Easily create the layout you want for your products 🎩</h2><br>
      <p>Show just <b data-feature-alt-demo="a">one product</b> at a time or <b data-feature-alt-demo="b">multiple products</b> in a list. Link your customers to <b data-feature-alt-demo="c">Shopify</b>, or show products in a <b data-feature-alt-demo="d">carousel</b> instead. You'll have endless possibilities, built with beautiful product layouts. Your customers will feel confident when purchasing with ShopWP.</p>
    </div>

    <div class="l-box-2" style="position: relative;left: 90px;top: -100px;">
      <div class="feature-demos">
          <div class="feature-demo-alt" data-feature-alt-demo="a">
              <?= do_shortcode('[wps_products title="Super*" limit="1" link_to="modal" link_target="_blank"]'); ?>
          </div>

          <div class="feature-demo-alt active" data-feature-alt-demo="b">
              <?= do_shortcode('[wps_products title="Super awesome tie, Super awesome t-shirt, Super awesome jacket" link_to="modal" connective="or" link_target="_blank" show_price_range="false" show_sale_notice="false" show_featured_only="false"]'); ?>
          </div>

          <div class="feature-demo-alt" data-feature-alt-demo="c">
              <?= do_shortcode('[wps_products title="Super awesome t-shirt" limit="1" link_to="shopify" variant_style="buttons"]'); ?>
          </div>

          <div class="feature-demo-alt" data-feature-alt-demo="d">
              <?= do_shortcode('[wps_products title="Super*" link_to="modal" carousel="true"]'); ?>
          </div>

      </div>
    </div>

  </div>
  
  <script>
      jQuery('.component-feature-highlights-alt p b').on('click', function() {

        var clickedDemo = jQuery(this).data('feature-alt-demo');

        console.log('clickedDemo', clickedDemo);

        jQuery('.feature-demo-alt').removeClass('active');
        jQuery('.feature-demo-alt[data-feature-alt-demo="' + clickedDemo + '"]').addClass('active')

      });
  </script>

</section>