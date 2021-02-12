<script src="https://cdn.jsdelivr.net/gh/cferdinandi/gumshoe@4/dist/gumshoe.polyfills.min.js"></script>
<script src="https://unpkg.com/in-view@0.6.1/dist/in-view.min.js"></script>

<section class="component component-features-demo">

   <header class="demo-header" id="component-features-demo">
      <h3>Demo WP Shopify Features 🎮 </h3>
      <p>Click one of the WP Shopify product features below to see a live example of how they work.</p>
   </header>
   <div class="row">

      <aside class="nav">
         <div class="nav-inner">
            <h4>Choose a feature</h4>
            <ul>
               <li>
                  <a href="#filter-by">Filter Products</a>
               </li>
               <li>
                  <a href="#sort-by">Sort Products</a>
               </li>
               <li>
                  <a href="#variant-dropdowns">Variant Dropdowns</a>
               </li>
               <li>
                  <a href="#link-to-shopify">Link to Shopify</a>
               </li>
               <li>
                  <a href="#sale-price">Sale Pricing</a>
               </li>
               <li>
                  <a href="#range-price">Range Pricing</a>
               </li>
               <li>
                  <a href="#pagination">Pagination</a>
               </li>
               <li>
                  <a href="#layout-excludes">Layout Excludes</a>
               </li>
               <li>
                  <a href="#out-of-stock-notice">Out of Stock Notice</a>
               </li>
               <li>
                  <a href="#cart-experience">Cart Experience</a>
               </li>
               <li>
                  <a href="#image-zoom">Image Zoom <span>(Pro only)</span></a>
               </li>
               <li>
                  <a href="#carousel">Carousel <span>(Pro only)</span></a>
               </li>
               <li>
                  <a href="#direct-checkout">Direct Checkout <span>(Pro only)</span></a>
               </li>
               <li>
                  <a href="#variant-buttons">Variant Buttons <span>(Pro only)</span></a>
               </li>
               <li>
                  <a href="#left-in-stock">Left in Stock Notice <span>(Pro only)</span></a>
               </li>
               <li>
                  <a href="#html-templates">HTML Templates <span>(Pro only)</span></a>
               </li>
               <li class="back-to-top">
                  <a href="#component-features-demo">👆 Back to top </a>
               </li>
            </ul>
         </div>
      </aside>
      
      <section class="features">
         <div id="filter-by">
            <h4>Filter Products Demo</h4>
            <div class="short-description">
               <p>WP Shopify allows you to filter products by title, tags, vendors, product types, and collections. You can also combine these to make complex searches. For example, "show products with X tag and Y vendor". Below we're showing a product filtered by the title: "Super awesome t-shirt".</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome t-shirt" show_price_range="false" link_target="_blank" link_to="shopify"]'); ?>
            </div>
         </div>
         <div id="sort-by">
            <h4>Sort Products Demo</h4>
            <div class="short-description">
               <p>In addition to filtering, WP Shopify allows you to sort products based on title, price, "best selling" and more. Below is an example of sorting products by price; lowest to highest.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super*" sort_by="price" show_price_range="true" link_target="_blank" link_to="shopify" limit="6"]'); ?>
            </div>
         </div>
         <div id="variant-dropdowns">
            <h4>Variant Dropdowns Demo</h4>
            <div class="short-description">
               <p>WP Shopify allows you to show product variants in a dropdown by default. The dropdown button colors can easily be customized to your liking to match your WordPress theme.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome jeans" show_price_range="false" variant_style="dropdown" add_to_cart_button_color="#2d45e6"]'); ?>
            </div>
         </div>
         <div id="link-to-shopify">
            <h4>Link to Shopify Demo</h4>
            <div class="short-description">
               <p>Using the "link to" feature, you can force your products to link directly to Shopify. This is a great strategy if you wish to simply show your products on a WordPress blog, while still keeping your Shopify storefront.</p>
            </div>
            <div class="example example-link-to">
               <?= do_shortcode('[wps_products title="Super awesome shades" link_target="_blank" show_price_range="false" link_to="shopify"]'); ?>
            </div>
         </div>

         <div id="sale-price">
            <h4>Sale Pricing Demo</h4>
            <div class="short-description">
               <p>Let customers know when your products are on sale by displaying effective sale pricing. WP Shopify will allow you to display the "compare at" price next to your standard price quickly and easily.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome jacket" link_to="shopify" link_target="_blank" show_price_range="false" show_compare_at="true"]'); ?>
            </div>
         </div>
         <div id="range-price">
            <h4>Range Pricing Demo</h4>
            <div class="short-description">
               <p>Sometimes you may want to let customers know that a product is sold in a wide range of prices. WP Shopify allows you to do this with range pricing. You can also combine this with sale pricing to give customers even more information.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome sunglasses" link_to="shopify" link_target="_blank" show_price_range="true" show_compare_at="true"]'); ?>
            </div>
         </div>

         <div id="pagination">
            <h4>Pagination Demo</h4>
            <div class="short-description">
               <p>Pagination is handled by WP Shopify with a simple load more button. You can easily turn this on or off on a per shortcode basis.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super*" page_size="3" link_to="shopify" link_target="_blank"]'); ?>
            </div>
         </div>

         <div id="layout-excludes">
            <h4>Excludes Demo</h4>
            <div class="short-description">
               <p>Sometimes you may not want to show all the product information in a given layout. WP Shopify provides the flexibility to easiy hide various parts of the product info including images, title, description, pricing, and buy button.</p>
            </div>
            <div class="example">
               <span>Showing: Title</span>
               <?= do_shortcode('[wps_products excludes="images, pricing, description, buy-button" title="Super awesome t-shirt" variant_style="buttons" show_compare_at="true" link_to="none"]'); ?>

               <span>Showing: Title, Images</span>
               <?= do_shortcode('[wps_products excludes="pricing, description, buy-button" title="Super awesome t-shirt" variant_style="buttons" show_compare_at="true" link_to="none"]'); ?>

               <span>Showing: Title, Images, Pricing</span>
               <?= do_shortcode('[wps_products excludes="description, buy-button" title="Super awesome t-shirt" variant_style="buttons" show_compare_at="true" link_to="none"]'); ?>

               <span>Showing: Title, Images, Pricing, Description</span>
               <?= do_shortcode('[wps_products excludes="buy-button" title="Super awesome t-shirt" variant_style="buttons" show_compare_at="true" link_to="none"]'); ?>

               <span>Showing: Title, Images, Pricing, Description, Buy Button</span>
               <?= do_shortcode('[wps_products excludes="false" title="Super awesome t-shirt" variant_style="dropdown" add_to_cart_button_color="#2d45e6" show_compare_at="true" link_to="none"]'); ?>

            </div>
         </div>

         <div id="out-of-stock-notice">
            <h4>Out of stock Notice Demo</h4>
            <div class="short-description">
               <p>Let your customers know when a product is out of stock. WP Shopify provides effective and simple "out of stock" messaging for you with no setup.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome tie" variant_style="buttons" show_compare_at="true"]'); ?>
            </div>
         </div>

         <div id="cart-experience">
            <h4>Cart experience</h4>
            <div class="short-description">
               <p>WP Shopify comes with a built-in cart experience with a ton of options including cart terms, cart notes, and discount code functionality. We also provide a simple to use cart icon that you can add to any page, or directly to a WordPress menu.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_cart_icon]'); ?>
            </div>
         </div>

         <div id="image-zoom">
            <h4>Image Zoom Demo</h4>
            <div class="short-description">
               <p>Let customers see your beautiful product photos with the <a href="/purchase" target="_blank">WP Shopify Pro</a> image zoom functionality. Compatible on both desktop and mobile, customers can hover or tap to take a closer look at each photo.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome t-shirt" variant_style="dropdown" add_to_cart_button_color="#2d45e6" show_compare_at="true" show_zoom="true"]'); ?>
            </div>
         </div>
         <div id="carousel">
            <h4>Carousel Demo</h4>
            <div class="short-description">
               <p>Showcase your products in a simple carousel without taking up precious space in your existing page layout. The <a href="/purchase" target="_blank">WP Shopify Pro</a> products carousel comes with a ton of options including the ability to custoimze previous / next arrows, number of slides to show, and more. <a href="https://docs.wpshop.io/#/shortcodes/wps_products?id=carousel?utm_medium=marketing-site&utm_source=component-features-demo&utm_campaign=info">See the docs</a>.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super*" carousel="true" excludes="description" link_target="_blank" carousel_slides_to_show="3" carousel_slides_to_scroll="3" link_to="shopify"]'); ?>
            </div>
         </div>
         <div id="direct-checkout">
            <h4>Direct Checkout Demo</h4>
            <div class="short-description">
               <p>By pass the cart altogether with the <a href="/purchase" target="_blank">WP Shopify Pro</a> direct checkout feature. Enabling this will redirect the user to the Shopify checkout page instead of adding to cart.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome t-shirt" direct_checkout="true" add_to_cart_button_color="#2d45e6"]'); ?>
            </div>
         </div>
         
         <div id="variant-buttons">
            <h4>Variant Buttons Demo</h4>
            <div class="short-description">
               <p><a href="/purchase" target="_blank">WP Shopify Pro</a> allows you to filter products by tags, vendors, product types, and collections. You can also filter by the product's title.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome t-shirt" variant_style="buttons"]'); ?>
            </div>
         </div>

         <div id="left-in-stock">
            <h4>Left in Stock Notice Demo</h4>
            <div class="short-description">
               <p>Develop urgency in your customers by showing them an effective "left in stock" message when product inventory is low.</p>
               <p>Select the variants below to see an example.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome jacket" variant_style="buttons" link_to="none"]'); ?>
            </div>
         </div>

         <div id="html-templates">
            <h4>HTML Templates Demo</h4>
            <div class="short-description">
               <p><a href="/purchase" target="_blank">WP Shopify Pro</a> allows you to customize the layout of products by using custom PHP templates. Just add a special <code>wps-templates</code> folder inside your WordPress theme and start building! <a href="https://docs.wpshop.io/#/guides/template-overriding" target="_blank">Learn more here</a>.</p>
            </div>
            <div class="example">
               <?= do_shortcode('[wps_products title="Super awesome t-shirt" html_template="product-custom.php" variant_style="buttons"]'); ?>
            </div>
         </div>

      </section>

   </div>

   <style>

      html {
         scroll-behavior: smooth;
      }

      .component-features-demo div[data-tippy-root] div.tippy-box {
         padding: 0;
         margin-top: -2px;
         background: white;
         border: 1px solid #313131;
         border-top: 0;
         left: 0;
         
         border-bottom-left-radius: 5px;
         border-bottom-right-radius: 5px;
         border-top-left-radius: 0;
         border-top-right-radius: 0;

         p {
            font-size: 14px;
         }
      }

      .component-features-demo div[data-tippy-root] div.tippy-box .wps-modal {
         overflow: hidden;
      }

      .wps-btn,
      .wps-btn-next-page {
         font-size: 16px !important;
      }


      .component-features-demo {
         background: white;
         margin: 8em auto;
         max-width: 100%;
         padding: 0;
      }

      .component-features-demo .back-to-top {
         border-top: 1px dotted #b4b4b4;
         margin-top: 10px !important;
         padding-top: 10px;
      }

      .component-features-demo .nav ul li.back-to-top a:hover {
         opacity: 1;
         background: transparent;
      }

      

      .component-features-demo .nav {
         width: 350px;
         margin-left: 115px;
         border-top-right-radius: 20px;
         border-top-left-radius: 20px;
         background-image: linear-gradient(0deg,#fff1f1 0,#f0f0ff);
      }

      .component-features-demo .nav-inner {
         top: 0;
         position: sticky;
         position: -webkit-sticky;
         padding-top: 25px;
      }

      .component-features-demo .nav ul {
         list-style: none;
         margin: 0;
         overflow: auto;
         position: relative;
         height: 100vh;
         padding-bottom: 60px;
      }

      .component-features-demo .demo-header {
         text-align: center;
         margin-bottom: 60px;
         padding-bottom: 10px;
         padding-top: 50px;
         margin-top: -50px;
         border-bottom: 1px solid #ddd;
         max-width: 800px;
         margin-left: auto;
         margin-right: auto;
      }

      .component-features-demo .demo-header p {
         font-size: 18px;
      }

      .component-features-demo .demo-header h3 {
         font-size: 32px;
      }

      .headroom--pinned ~ .main .component-features-demo .features,
      .headroom--pinned ~ .main .component-features-demo .nav-inner {
         transform: translateY(80px);
      }

      .headroom--pinned ~ .main .component-features-demo .nav-inner {
         margin-bottom: 80px;
      }

      .headroom--pinned ~ .main .component-features-demo .demo-header {
         margin-bottom: -80px;
      }
          

      .component-features-demo .nav ul li {
         margin-top: 0;
             
      }

      .component-features-demo .short-description {
         padding-bottom: 10px;
         margin-bottom: 10px;
         margin-top: 0;
      }

      .component-features-demo .nav ul li a {
         color: black;
         line-height: 1;
         padding: 13px 0px 13px 45px;
         display: block;
         text-decoration: none;
         font-size: 16px;
         position: relative;
         font-family: Metropolis,helvetica;
      }

      .component-features-demo .nav ul li a span {
         font-size: 13px;
         color: #8b8b8b;
         margin-left: 5px;
      }

      .component-features-demo .nav h4 {
         font-weight: bold;
         font-size: 20px;
         padding-left: 45px;
         margin-bottom: 15px;
      }



      .component-features-demo .nav ul li a:hover {
         opacity: 1;
         background: white;
      }

      .component-features-demo .nav ul li.active a {
         background: white;
         transition: all ease 0.15s;
      }

      .component-features-demo .nav ul li.active a:after {
         content: '👀';
         position: absolute;
         right: 35px;
         top: 7px;
         font-size: 28px;
      }

      .component-features-demo .nav ul li.back-to-top.active a {
         background: transparent;
      }

      .component-features-demo .nav ul li.back-to-top.active a:after {
         content: '';
      }

      .component-features-demo .row {
         display: flex;
      }

      .component-features-demo .content > .btn {
         font-size: 1em;
      }

      .component-features-demo .features {
         flex: 1;
         margin-top: 0;
         padding: 0 65px 25px 65px;
      }

      .component-features-demo .features > div {
         margin-top: 0;
         margin-bottom: 3em;
         padding-bottom: 3em;
         padding-top: 20px;
         margin-top: -20px;
         border-bottom: 1px solid #ddd;
      }

      .component-features-demo .features > div:last-of-type {
         padding-bottom: 0;
         border: 0;
      }

      .component-features-demo .features > div h4 {
         font-weight: bold;
         font-size: 20px;
      }
      .component-features-demo .features > div p {
         max-width: 700px;
      }

      .wps-cart-icon-fixed {display: none;}

      .wps-component-products-images-thumbnail {margin-top: 0;}
      .wps-item *+* {
         margin-top: 0;
      }

      .wps-product-prices-compare-at {
         margin-top: 10px;
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
      
      .component-features-demo .demo .wpshopify-loading-placeholder + div {
         max-width: 300px;
         margin: 0 auto;
      }

      .wpshopify .wps-cart-item__quantity, .wpshopify .wps-cart-lineitem-quantity {
         padding: 0;
      }

      .component-features-demo .content .form-error .error {
         margin-top: 0;
      }

      .component-features-demo .content h2 {
         position: relative;
      }

      .component-features-demo .example {
         max-width: 700px;
         margin-top: 0;
      }

      .component-features-demo .wps-product-from-price,
      .component-features-demo .wps-pricing-sale-notice,
      .component-features-demo .wps-pricing-sale-price {
         font-style: normal;
      }

      .component-features-demo label[for="wps-product-quantity"] {
         font-weight: normal;
         font-size: 15px;         
      }

      .wps-item .wps-component-products-title .wps-products-title {
         font-size: 20px;
         font-weight: bold;
         line-height: 1.3;
         font-family: Metropolis,helvetica;
      }

      .custom-content .wps-component-products-description {
         line-height: 1.6;
      }

      .custom-content .wps-component-products-pricing {
         margin-bottom: 25px;
      }

      .wps-btn-cart .wps-icon {
         margin-top: 0;
      }

      .example > span {
         margin-bottom: 13px;
         display: block;
         font-size: 14px;
         font-weight: bold;
         border-bottom: 1px solid #ddd;
         padding-bottom: 8px;
      }

      .custom-row {display: flex;}
      .custom-images {width: 300px;}
      .custom-content{flex: 1; margin-left: 30px;}

      #html-templates .wps-items-list {
         max-width: 100%;
         width: 100%;
      }

      @media (max-width: 1200px) {
         .component-features-demo .nav {
            margin-left: 0;
            border-top-left-radius: 0;
            width: 290px;
         }
      }

   </style>

   <script>
      window.addEventListener('DOMContentLoaded', function() {
         var spy = new Gumshoe('.component-features-demo .nav li a', {
            offset: 75
         });
      });

      inView('.component-features-demo')
      .on('enter', function(element) {
         jQuery(element).addClass('is-visible')
      })
      .on('exit', function(element) {
         jQuery(element).removeClass('is-visible')
      });
      
      wp.hooks.addAction('after.cart.ready', 'wpshopify', function (cartState) {
         jQuery('.example:not(.example-link-to) .wps-products-link').attr('href', 'https://www.shopify.com/?ref=wps');
      });

   </script>

</section>