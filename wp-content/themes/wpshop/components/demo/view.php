<section class="component component-demo">
   <div class="row">
      <div class="demo">
         <?= do_shortcode('[wps_products title="Super awesome t-shirt" variant_style="buttons" show_zoom="true" show_compare_at="true"]'); ?>
      </div>
      <div class="content">
         <h2>See it in action!</h2>
         <p>WP Shopify provides a completely seamless way to embed your products. Display a single buy button, or a whole list of products with ease.</p>
         <p>No need to worry about the checkout or cart experience either. WP Shopify creates these for you out of the box.</p>
         <span class="btn btn-l btn-download-free getting-started-trigger">Try for free</span>
      </div>
   </div>
   <style>

      .component-demo {
         background: white;
         margin: 7em auto;
         max-width: 1120px;
      }
      .component-demo .row {
         display: flex;
      }

      .component-demo .demo {
         width: 470px;
      }

      .component-demo .content > .btn {
         font-size: 1em;
      }

      .component-demo .content {
         flex: 1;
         padding-top: 10vw;
         font-size: 20px;
      }

      .wps-cart-icon-fixed {display: none;}
      .wps-component-products-images-thumbnail {margin-top: 0;}
      .wps-item *+* {
         margin-top: 0;
      }

      .wps-item .wps-thumbnails-wrapper {
         margin-top: 13px !important;
      }

      .wpshopify-variant-buttons button {
         line-height: 1;
      }

      .wps-cart *+* {
         margin-top: 0;
      }
      
      .component-demo .demo .wpshopify-loading-placeholder + div {
         max-width: 300px;
         margin: 0 auto;
         position: relative;
         left: 5vw;
      }

      .wpshopify .wps-cart-item__quantity, .wpshopify .wps-cart-lineitem-quantity {
         padding: 0;
      }

      .component-demo .content .form-error .error {
         margin-top: 0;
      }

      .component-demo .content h2 {
         position: relative;
      }

      .component-demo .content h2 svg {
         position: absolute;
         top: -95px;
         font-size: 75px;
         left: -53px;
         transform: rotate(-23deg);
      }

      .component-demo .wps-product-from-price,
      .component-demo .wps-pricing-sale-notice,
      .component-demo .wps-pricing-sale-price {
         font-style: normal;
      }

      .component-demo label[for="wps-product-quantity"] {
         font-weight: normal;
         font-size: 15px;         
      }

   </style>

</section>