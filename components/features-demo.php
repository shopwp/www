<?php 

$Products       = ShopWP\Factories\Render\Products\Products_Factory::build();
$Storefront     = ShopWP\Factories\Render\Storefront\Storefront_Factory::build();
$Search         = ShopWP\Factories\Render\Search\Search_Factory::build();
$Cart           = ShopWP\Factories\Render\Cart\Cart_Factory::build();

$selected_feat = empty($_GET['feat']) ? 'storefront' : $_GET['feat'];

?>

<section class="component component-full component-features-demo nav-screens" data-selected-feat="<?= $selected_feat; ?>">
    
    <div class="l-row l-row-center l-col-center l-contain-s features-demo-selector">

        <label for="demo-selector">Choose a feature:</label>

        <select name="pets" id="demo-selector">
            <option value="storefront">Storefront</option>
            <option value="search">Search</option>
            <option value="cart-icon">Cart Icon</option>
            <option value="image-zoom">Image Zoom</option>
            <option value="next-on-hover">Next Image On Hover</option>
            <option value="direct-checkout">Direct Checkout</option>
            <option value="carousel">Carousel</option>
            <option value="subscriptions">Subscription Products</option>
            <option value="select-on-load">Select variant on load</option>
            <option value="html-template">Custom HTML Template</option>
            <option value="layout-builder">Layout Builder</option>
            <option value="buy-buttons">Buy Buttons</option>
            <option value="shortcodes">Shortcodes</option>
            <option value="cpt">Custom post types</option>
            <option value="blocks">Gutenberg Blocks</option>
            <option value="syncing">Syncing</option>
        </select>

    </div>

    <div class="l-contain-s screen screen-info" data-screen="storefront" data-is-selected="true">
        <h2 class="l-row l-row-center l-contain-s">Storefront</h2>
        <p>This is the Storefront component. It allows you to display products with filtering, sorting, and pagination&mdash;built right in! Let your users find your products easily and quickly.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="select-on-load">
        <h2 class="l-row l-row-center l-contain-s">Select variant on load</h2>
        <p>Automatically select any product variant on page load to make it easier for your customers to purchase your best selling products. You can also use the ?variant=123 URL parameter.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="html-template">
        <h2 class="l-row l-row-center l-contain-s">HTML Template</h2>
        <p>Customize the product layout of any shortcode to suit your needs. Simply pass a custom HTML template name or a base64 encoded string on the shortcode. <a href="https://docs.wpshop.io/guides/html-templates">Learn more</a> about how this works.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="search">
        <h2 class="l-row l-row-center l-contain-s">Search</h2>
        <p>This is the Search component. It allows your customers to explore your entire Shopify catalog with a simple dynamic search. Try typing the word "Super" below.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="cart-icon">
        <h2 class="l-row l-row-center l-contain-s">Cart Icon</h2>
        <p>Embed a simple cart icon anywhere on your site. When users click it, the ShopWP slide-in cart will automatically toggle. Try it yourself! <a href="https://docs.wpshop.io/shortcodes/wps_cart" class="link link-external" target="_blank">Learn more</a></p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="image-zoom">
        <h2 class="l-row l-row-center l-contain-s">Image Zoom</h2>
        <p>Give customers a closer look at your beautiful products by enabling the ShopWP image zoom feature. Hover over the image below to demo!</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="next-on-hover">
        <h2 class="l-row l-row-center l-contain-s">Next image on hover</h2>
        <p>Show the second product image when a user hovers over the feature image </p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="direct-checkout">
        <h2 class="l-row l-row-center l-contain-s">Direct Checkout</h2>
        <p>Skip the ShopWP cart altogether using the Direct Checkout feature. Clicking the checkout button will send customers to the Shopify checkout page with the specific product they want to buy. Perfect if you only need to sell a handful of products.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="carousel">
        <h2 class="l-row l-row-center l-contain-s">Carousel</h2>
        <p>If space is limited, display your products in an easy to navigate carousel. You can customize your carousel's speed, slide count, and <a href="https://docs.wpshop.io/shortcodes/wps_products#carousel" target="_blank">much more</a>.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="subscriptions">
        <h2 class="l-row l-row-center l-contain-s">Subscription Products</h2>
        <p>ShopWP lets you sell subscription products via <a href="/extensions/recharge">Recharge</a>&mdash;one of the most popular subscription platforms on Shopify. Requires our <a href="/extensions/recharge">official plugin extension</a>.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="layout-builder">
        <h2 class="l-row l-row-center l-contain-s">Layout Builder</h2>
        <p>ShopWP provides a powerful visual builder for creating product layouts. Customize your product detail pages or create a simple one-off layout.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="buy-buttons">
        <h2 class="l-row l-row-center l-contain-s">Buy Buttons</h2>
        <p>Add an isolated buy button for any product&mdash;wherever you want. Control how the variants look and where the buy button links. Link to a cart, checkout, modal, or product detail page.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="shortcodes">
        <h2 class="l-row l-row-center l-contain-s">Shortcodes</h2>
        <p>With 12 different shortcodes, ShopWP can provide all the functionality and layout flexibility you need for you shop.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="cpt">
        <h2 class="l-row l-row-center l-contain-s">Custom post types</h2>
        <p>ShopWP can sync products and collections as native WordPress custom post types to easily create detail pages for your store.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="blocks">
        <h2 class="l-row l-row-center l-contain-s">Gutenberg Blocks</h2>
        <p>ShopWP comes with 13 unique Gutenberg Blocks for displaying products and collections. All blocks will inherit attributes from the available shortcodes.</p>
    </div>

    <div class="l-contain-s screen screen-info" data-screen="syncing">
        <h2 class="l-row l-row-center l-contain-s">Syncing</h2>
        <p>Sync your Shopify products into WordPress including prices, images, collections and more. All created as native custom post types and taxonomies.</p>
    </div>

    <div class="l-col l-contain l-sb">
        
        <div class="features-demo-content l-flex">
            <div class="demo">

                <div class="screen" data-screen="syncing">
                
                    <figure>
                        <img src="/wp-content/uploads/2023/06/syncing-1.png" alt="ShopWP syncing process working" />
                        <figcaption>A screenshot showing the ShopWP syncing in progress</figcaption>
                    </figure>

                    <figure>
                        <img src="/wp-content/uploads/2023/06/syncing-2.png" alt="ShopWP syncing settings page" />
                        <figcaption>A screenshot showing the ShopWP syncing settings</figcaption>
                    </figure>

                </div>

                <div class="screen" data-screen="storefront" data-is-selected="true">
                    <?php 
                    
                    $Storefront->storefront([
                        'filter_option_open_on_load'    => 'collections',
                        'variant_style'                 => 'buttons',
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="layout-builder">

                    <img src="/wp-content/uploads/2023/06/layout-builder-with-annotations.jpg" alt="Layout builder with annotations" />

                    <dl class="list-annotations">
                        <li class="l-row">
                            <dt>1</dt>
                            <dd>This is where you give your layout an awesome name. It's nothing fancy, just a WordPress page title field.</dd>
                        </li>
                        <li class="l-row">
                            <dt>2</dt>
                            <dd>This is where useful information about your layout will appear. If you're making a template layout, a link will be shown to that template. If you're creating a shortcode layout the generated shortcode will show instead.</dd>
                        </li>
                        <li class="l-row">
                            <dt>3</dt>
                            <dd>This is the layout summery panel. Here you can select the layout type (shortcode or template), preview your layout in different products, or select the type of shortcode to build.</dd>
                        </li>
                        <li class="l-row">
                            <dt>4</dt>
                            <dd>The block tab provides the actual layout settings such as font color, filtering, grid spacing, etc. These settings are used with the ShopWP blocks.</dd>
                        </li>
                        <li class="l-row">
                            <dt>5</dt>
                            <dd>The reset button will allow you to "reset" your layout back to the default settings. Useful for starting your design over again from scratch.</dd>
                        </li>
                        <li class="l-row">
                            <dt>6</dt>
                            <dd>These are your individual product components. Drag and drop elements like your product title or buy button into a layout that you create. You will also have access to the entire library of blocks to use.</dd>
                        </li>
                    </dl>

                </div>

                <div class="screen" data-screen="cpt">                    
                    <figure>
                        <img src="/wp-content/uploads/2023/06/shopwp-custom-post-types.png" alt="ShopWP custom post types listing page" />
                        <figcaption>Custom post type listing page</figcaption>
                    </figure>

                    <figure>
                        <img src="/wp-content/uploads/2023/06/cpt-2.png" alt="ShopWP custom post types edit page" />
                        <figcaption>Custom post type edit page</figcaption>
                    </figure>
                </div>

                <div class="screen" data-screen="blocks">
                    <figure>
                        <img src="/wp-content/uploads/2023/06/gutenberg-blocks.jpg" alt="ShopWP Gutenberg Blocks" />
                        <figcaption>Screenshots showing the ShopWP Gutenberg blocks and controls</figcaption>
                    </figure>
                </div>

                <div class="screen" data-screen="shortcodes">
                    <dl class="list-simple l-contain-s">
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_products" target="_blank">[wps_products]</a></dt>
                            <dd>Displays one or more products in a list or grid. This is perfect for filtering and sorting many products.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_products_title" target="_blank">[wps_products_title]</a></dt>
                            <dd>Displays one or more product titles in a list or grid. Useful for only showing product titles.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_products_pricing" target="_blank">[wps_products_pricing]</a></dt>
                            <dd>Displays one or more product prices in a list or grid. Useful for only showing product pricing.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_products_gallery" target="_blank">[wps_products_gallery]</a></dt>
                            <dd>Displays one or more product images in a list or grid. Useful for only showing product images.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_products_description" target="_blank">[wps_products_description]</a></dt>
                            <dd>Displays one or more product descriptions in a list or grid. Useful for only showing product description.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_products_buy_button" target="_blank">[wps_products_buy_button]</a></dt>
                            <dd>Displays one or more product buy buttons in a list or grid. Useful for only showing the buy button.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_collections" target="_blank">[wps_collections]</a></dt>
                            <dd>Displays one or more collections in a list or grid. This is perfect for filtering and sorting many collections.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_cart_icon" target="_blank">[wps_cart_icon]</a></dt>
                            <dd>Displays a cart icon that opens the ShopWP cart when clicked.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_search" target="_blank">[wps_search]</a></dt>
                            <dd>Displays a dynamic search form which will show products immediately as the user types what they're looking for.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_storefront" target="_blank">[wps_storefront]</a></dt>
                            <dd>Displays a grid of products with filtering and sorting functionality in a sidebar.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_reviews" target="_blank">[wps_reviews]</a>&mdash;(with <a href="/extensions/yotpo-product-reviews/">Yotpo extension</a>)</dt>
                            <dd>Displays product reviews as well as an option to write a new review. Powered by Yotpo.</dd>
                        </li>
                        <li>
                            <dt><a href="https://docs.wpshop.io/shortcodes/wps_translator" target="_blank">[wps_translator]</a>&mdash;(with <a href="/extensions/translator/">Translator extension</a>)</dt>
                            <dd>Displays a widget for customers to switch between different currencies and languages.</dd>
                        </li>
                    <ul>
                </div>

                <div class="screen l-contain-s" data-screen="search">
                    <?php 
                    
                    $Search->search([
                        'link_to'                   => 'modal',
                        'search_placeholder_text'   => 'Try searching the word "super"'
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="buy-buttons">


                    <div class="l-row l-row-center">
                        <div class="l-col l-box-3">
                            <p class="features-demo-label">Variant dropdowns</p>
                            <?php 
                            
                            $Products->buy_button([
                                'link_to'           => 'none',
                                'title'             => 'Super awesome jacket',
                                'variant_style'     => 'dropdown',
                            ]);

                            ?>
                        </div>

                        <div class="l-col l-box-3">
                            <p class="features-demo-label">Variant buttons</p>
                            <?php 
                            
                            $Products->buy_button([
                                'link_to'           => 'none',
                                'variant_style'     => 'buttons',
                                'title'             => 'Super awesome jacket'
                            ]);

                            ?>
                        </div>

                        <div class="l-col l-box-3">
                            <p class="features-demo-label">Link to modal</p>
                            <?php 
                            
                            $Products->buy_button([
                                'link_to'   => 'modal',
                                'title'     => 'Super awesome jacket'
                            ]);

                            ?>
                        </div>                       
                        
                    </div>

                    <div class="l-row l-row-center" style="margin-top: 50px;">
                        <div class="l-col l-box-3">
                            <p class="features-demo-label">Link to Shopify</p>
                            <?php 
                            
                            $Products->buy_button([
                                'link_to'           => 'shopify',
                                'link_target'       => '_blank',
                                'title'             => 'Super awesome jacket'
                            ]);

                            ?>
                        </div>

                        <div class="l-col l-box-3">
                            <p class="features-demo-label">Link to checkout page</p>
                            <?php 
                            
                            $Products->buy_button([
                                'direct_checkout'   => true,
                                'link_target'       => '_blank',
                                'title'             => 'Super awesome jacket'
                            ]);

                            ?>
                        </div>

                    </div>

                </div>

                <div class="screen" data-screen="cart-icon">
                    <?php 
                    
                    $Cart->cart_icon();
                    
                    ?>
                </div>

                <div class="screen" data-screen="image-zoom">
                    <?php 
                    
                    $Products->products([
                        'show_zoom'             => true,
                        'title'                 => 'Super awesome t-shirt',
                        'excludes'              => ['description', 'buy-button', 'title', 'pricing'],
                        'show_featured_only'    => false,
                        'show_sale_notice'      => false,
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="next-on-hover">
                    <?php 
                    
                    $Products->products([
                        'title'                     => 'Super awesome t-shirt',
                        'excludes'                  => ['description', 'buy-button', 'title', 'pricing'],
                        'show_featured_only'        => true,
                        'images_show_next_on_hover' => true,
                        'show_sale_notice'          => false
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="direct-checkout">
                    <?php 
                    
                    $Products->products([
                        'direct_checkout'       => true,
                        'title'                 => 'Super awesome t-shirt',
                        'show_featured_only'    => true,
                        'variant_style'         => 'buttons',
                        'link_target'           => '_blank'
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="carousel">
                    <?php 
                    
                    $Products->products([
                        'carousel'                      => true,
                        'carousel_slides_to_show'       => 4,
                        'collection'                    => 'super',
                        'show_featured_only'            => true,
                        'link_to'                       => 'modal',
                        'link_target'                   => '_blank',
                        'align_height'                  => true,
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="subscriptions">
                    <?php 
                    
                    $Products->products([
                        'title'             => 'Super awesome jacket',
                        'subscriptions'     => true,
                        'variant_style'     => 'buttons'
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="select-on-load">
                    <?php 
                    
                    $Products->products([
                        'title'                 => 'Super awesome dress shirt',
                        'variant_style'         => 'buttons',
                        'select_first_variant'  => true
                    ]);
                    
                    ?>
                </div>

                <div class="screen" data-screen="html-template">

                    <?php 
                    
                    $Products->products([
                        'title'                 => 'Super awesome dress shirt',
                        'variant_style'         => 'buttons',
                        'html_template_data'    => 'PGRpdiBjbGFzcz0nbC1yb3cnPgogICAgPGRpdiBjbGFzcz0nbC1ib3gtMic+CiAgICAgICAgPFByb2R1Y3RJbWFnZXMgLz4KICAgIDwvZGl2PgogICAgPGRpdiBjbGFzcz0nbC1ib3gtMic+CiAgICAgICAgPFByb2R1Y3RUaXRsZSAvPgogICAgICAgIDxQcm9kdWN0UHJpY2luZyAvPgogICAgICAgIDxQcm9kdWN0QnV5QnV0dG9uIC8+CiAgICA8L2Rpdj4KPC9kaXY+'
                    ]);
                    
                    ?>
                    <div class="l-row l-contain">
                        <div class="l-box-2">
                            <b>Shortcode:</b>
                            <figure>
                                <img src="/wp-content/uploads/2023/10/template-shortcode-example.png" alt="ShopWP HTML template shortcode example" />
                            </figure>
                        </div>
                        <div class="l-box-2">
                            <b>Template:</b>
                            <figure>
                                <img src="/wp-content/uploads/2023/10/template-code-example.png" alt="ShopWP HTML template file example" />
                            </figure>
                        </div>
                    </div>                    
                </div>

            </div>
        </div>
    </div>
</section>