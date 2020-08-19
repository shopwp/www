<section class="component component-demo">
   <div class="row">
      <div class="demo">
         <?= do_shortcode('[wps_products title="Super awesome jeans" variant_style="buttons" show_zoom="true" show_compare_at="true"]'); ?>
      </div>
      <div class="content">
         <h2>See it in action!</h2>
         <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
         <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
         <span class="btn btn-l btn-download-free getting-started-trigger">Download the plugin</span>
      </div>
   </div>
   <style>

      .component-demo {
         background: white;
         margin: 7em 0;         
      }
      .component-demo .row {
         display: flex;
      }

      .component-demo .demo {
         width: 57%;
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

      .component-demo .wps-items {
         position: relative;
         left: 5vw;         
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