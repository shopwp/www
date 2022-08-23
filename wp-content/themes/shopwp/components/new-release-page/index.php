<style>

    .main {
        margin-top: 90px;
    }

    .component.component-new-release {
        max-width: 100%;
        padding: 0;
    }

    .release-marquee {
        width: 100%;
        background: #0f0c29;
        background: -webkit-linear-gradient(to right, #24243e, #0a006e, #0f0c29);
        background: linear-gradient(to right, #24243e, #0a006e, #0f0c29);
        color: white;
    }

    .release-marquee-inner {
        text-align: center;
        padding: 80px 0;
        position: relative;
        
    }

    .release-marquee-inner span {
        position: absolute;
        right: 20%;
        font-size: 48px;
        top: 58px;
        text-shadow: 0 0 25px #d6fffb40;
    }

    .release-marquee h1 {
        color: white;
        font-size: 100px;
        font-weight: 900;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-image: linear-gradient(43deg, #4158D0 0%, #ff71f5 46%, #FFCC70 100%);
        text-shadow: 0 0 25px #d6fffb40;
    }

    .release-marquee p {
        font-size: 28px;
        display: inline-block;
        position: relative;
        width: 650px;
        text-shadow: 0 0 25px #d6fffb40;
    }

    .release-marquee-intro {
        padding: 40px 0;
    }

    .release-marquee-intro small,
    .release-marquee-intro h2 {
        font-size: 20px;
        font-weight: normal;
        line-height: 1.6;
        max-width: 800px;
        margin: 0 auto;
    }

    .release-marquee-intro small {
        display: block;
        font-size: 14px;
        font-style: italic;
        margin-top: -20px;
        margin-bottom: 25px;
        color: #909090;
    }

    .release-content-section {
        margin: 20px 0 80px 0;
    }

    .release-content-section .l-contain {
        max-width: 800px;
    }

    .release-content-image img {
        border-radius: 20px;
        box-shadow: 0 5px 32px -2px rgb(0 0 0 / 31%);
    }

    .release-marquee-footer {
        padding: 40px 15px 47px 15px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        background: #E0EAFC;
        background: -webkit-linear-gradient(to right, #CFDEF3, #E0EAFC);
        background: linear-gradient(to right, #CFDEF3, #E0EAFC);
        width: 100%;
        max-width: 100%;
        border-radius: 0;
        margin: 80px 0 0 0;
    }

    .release-marquee-footer h3 {
        font-size: 32px;
    }

    .release-marquee-footer svg {
        width: 186px;
        position: absolute;
        left: 148px;
        transform: rotate(18deg);
        top: -2px;
    }

    .release-marquee-footer .btn {
        text-align: center;
        font-size: 23px !important;
        margin-bottom: 5px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .release-marquee-footer .btn:hover {
        border: none;
        box-shadow: none;
        opacity: 0.8;
        background-color: #5246e1;
    }

    .release-marquee-footer .icon-scwiggle {
        position: absolute;
        right: 171px;
        top: 28px;
        width: 61px;
    }

    .release-marquee-footer .l-contain {
        position: relative;
    }

</style>

<section class="component component-new-release">
   
    <div class="release-marquee">
        <div class="release-marquee-inner l-contain">
            <span>🎉</span>
            <h1>ShopWP 6.0</h1>
            <p>Data syncing like you've never seen before</p>
        </div>
    </div>

    <div class="release-marquee-intro l-contain">
        <small>Posted August 23rd, 2022</small>
        <h2>ShopWP 6.0 is here!<br><br>
        Available today is a monumental update to the plugin syncing. The entire process has been re-built from the ground up.<br><br>Some of the most notable changes are: auto syncing, native WordPress taxonomies, image sync and much more. <br><br>Let's take a closer look at each one.</h2>
    </div>

   <div class="release-marquee-body l-contain">
    
        <div class="release-content-section">
            <div class="l-contain">
                <h3>UI changes 👀</h3>
                <p>One giant change is the overall syncing UI. As you can see in the below screenshot, there is now much more information visible while the sync is running.</p>

                <p>The big grey box in the middle is called the Sync Log. It will show you a detailed breakdown of what's actually happening during the sync. You can choose to show all info, or just warnings / errors by clicking the visibility icon as well.</p>

                <p>Once the sync is finished, you'll have the option of downloading the sync log. This can really help you debug any issues that may come up. The messages found inside this sync log are only related to the syncing and nothing else.</p>

                <p>Below the grey box is information regarding which data is actually being synced. My hope is that this helps remove any ambiguity during the sync.</p>
                
                <p></p>
            </div>
            <div class="release-content-image">
                <img src="/wp-content/themes/shopwp/assets/imgs/new-release-1.png" />
            </div>
        </div>

        <div class="release-content-section">
            <div class="l-contain">
                <h3>Auto syncing 🕗</h3>
                <p>This is probably my favorite new feature.</p>
                <p>Prior to version 6.0, auto syncing was handled by webhooks. This worked (usually), but had issues and limitations on some web hosts. In version 6.0, auto syncing has been re-built and is now a standard WordPress cron job.</p>
                <p>You'll be able to set the cron interval (how often you want the sync to run), as well as the sync query. As long as the admin pages are loaded frequently, the cron will continue to run.</p>
                <p>Behind the scenes, ShopWP is using <a href="https://shopify.dev/api/usage/bulk-operations/queries" target="_blank">Shopify Bulk Operations</a>. This leverages the Shopify servers to do all the heavy work of requesting data. It essentially allows ShopWP to perform very large data queries via GraphQL without being limited by pagination.</p>
            </div>
            <div class="release-content-image">
                <img src="/wp-content/themes/shopwp/assets/imgs/syncing-screenshot-3.png" />
            </div>
        </div>

        <div class="release-content-section">
            <div class="l-contain">
                <h3>Upgraded custom post types 📖</h3>
                <p>The ShopWP custom post types for products and collections have been massively upgraded. Let's take a look!</p>
                <p>Within the custom post type UI, you'll now see your Shopify taxonomies reflected as native WordPress taxonomies! This has been one of the most requested features, and will help tremendously for integrating with other plugins like Yoast, ACF, page builders, etc.</p>
                <p>As you can see in the screenshots below, Your Shopify vendors, tags, collections, and product types are transferred as standard WordPress taxonomies. If you choose to delete the data from WordPress, these taxonomies will also be removed. No mess left behind.</p>
            </div>
            <div class="release-content-image">
                <img src="/wp-content/themes/shopwp/assets/imgs/syncing-screenshot-4.png" />
                <img src="/wp-content/themes/shopwp/assets/imgs/syncing-screenshot-5.png" />
            </div>
        </div>

        <div class="release-content-section">
            <div class="l-contain">
                <h3>Image syncing 🌅</h3>
                <p>Your Shopify images will now sync natively into WordPress!</p>
                <p>You'll have the option of not syncing images, only syncing featured images, or all images directly into the media library.</p>
                <p>This will help you use the WordPress functions like <b>get_the_post_thumbnail()</b> and <b>the_post_thumbnail()</b> within custom loops.</p>
                <p>Choosing to sync images will slow the sync down, but you can just keep it running for as long as it takes to finish. You can also choose to sync everything else first, and then sync images by themselves if you wish.</p>
            </div>
            <div class="release-content-image">
                <img src="/wp-content/themes/shopwp/assets/imgs/syncing-screenshot-8.png" />
                <img src="/wp-content/themes/shopwp/assets/imgs/syncing-screenshot-9.png" />
            </div>
        </div>

        <div class="release-content-section">
            <div class="l-contain">
                <h3>Post meta data 💾</h3>
                <p>You now have the option of syncing much of your product and collection data into post meta. This feature is turned off by default, but you can enable it within the plugin settings.</p>
                <p>Effortlessly integrate your products and collections with other plugins like Elementor, Yoast, or ACF. The only limitation is your imagination when building out custom themes and product layouts.</p>
            </div>
            <div class="release-content-image">
                <img src="/wp-content/themes/shopwp/assets/imgs/syncing-screenshot-7.png" />
            </div>
        </div>

        <div class="release-content-section">
            <div class="l-contain">
                <h3>Better syncing controls 🎛</h3>
                <p>Many of the existing syncing settings have been revamped and greatly improved.</p>
                <p>One really cool addition is the "Products sync query" setting. This allows you to specify with powerful granularity which products to sync.</p>
                <p>The plugin uses the <a href="https://shopify.dev/api/usage/search-syntax" target="_blank">Shopify search syntax</a> for this, and empowers users to adjust this however they wish. Sync all products with the asterisks (*), or only products from a certain tag: (tag:books).</p>
                <p>Another new addition to the syncing settings are the data selections. This allows you to <i>sync only the data you really want</i> and nothing more.</p>
            </div>
            <div class="release-content-image">
                <img src="/wp-content/themes/shopwp/assets/imgs/syncing-screenshot-6.png" />
            </div>
        </div>

        <div class="release-content-section">
            <div class="l-contain">
                <h3>Other plugin improvements and bug fixes in 6.0 🪲</h3>
                <ul>
                    <li><b>Fixed:</b> an issue where the product thumbnails would occasionally not show
                    <li><b>Fixed:</b> JSON parsing error due to conflict with All-in-one security plugin</li>
                    <li><b>Fixed:</b> Product gallery will now correctly show all images </li>
                    <li><b>Fixed:</b> Load order of the on.cartLoad JavaScript action.  </li>
                    <li><b>Fixed:</b> Incorrectly showing the Yotpo API keys notice when Yotpo reviews not installed</li>
                    <li><b>Fixed:</b> Bug causing ShopWP custom tables to remain after deleting plugin </li>
                    <li><b>Improved:</b> Updated the wizard copy </li>
                    <li><b>Improved:</b> buyer_identity option is no longer autoloaded</li>
                    <li><b>Improved:</b> Added lock icons to ShopWP Pro only buttons</li>
                    <li><b>Improved:</b> Changed the "disconnected" status color to grey instead of red </li>
                    <li><b>Updated:</b> The EDD updater class has been updated to 1.9.2</li>
                    <li><b>Dev:</b> Added tags to product payload</li>
                    <li><b>Dev:</b> Fixed typo in Localizations query </li>
                </ul>

            </div>
        </div>

   </div>

   <div class="release-marquee-footer l-contain">
        
        <div class="l-contain l-row l-row-center">
            <h3>Not a Pro user yet?</h3>
        </div>
        <div class="l-contain l-row l-row-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 174 76" xml:space="preserve"><path d="M11.1 69.5c-.1.4-.2.8.1 1 .4.3.7 0 1-.3.6-.7 1.2-1.5 1.8-2.3 3.9-5.8 7.7-11.7 11.3-17.8 5.2-8.9 12.2-16.3 19.3-23.7 1.9-1.9 3.8-3.7 6-5.3.4-.3.7-.8 1.4-.5-.1.4-.2.9-.3 1.3-1.3 4.3-2.7 8.6-4 12.9-1.6 5.2-3.2 10.3-4 15.7-.6 3.3-.7 6.5.6 9.5 1.3 2.9 3.6 3.8 6.6 2.6 1.4-.6 2.6-1.5 3.8-2.3 7-5 13-11.2 19.4-16.9 10-8.9 21-16.3 33.6-21.1 7.8-3 15.9-5.3 24.3-6 8-.6 16 .1 24.1.5-.3 1-1.1 1.2-1.7 1.6-1.3.9-2.5 1.8-3.6 2.9-.4.4-.7 1-.4 1.6.3.7 1 .9 1.7.7.9-.2 1.7-.5 2.5-.8 4.3-1.8 8.1-4.6 12.4-6.4 1.6-.6 2-2.1 1.6-3.7-.4-1.7-1.5-2.4-3-2.9-3.9-1.2-7.8-2.3-11.6-3.9-1-.4-2-.6-3-.9-.8-.2-1.7-.2-2.2.7-.6 1 0 1.7.7 2.3.7.7 1.6 1.3 2.5 1.8 1 .6 2 1.2 3.3 2-2.3 0-4.3.1-6.1 0-7.2-.5-14.5-.7-21.7.3-19.5 2.8-36.5 11-51.6 23.2-4.6 3.7-8.8 7.9-13.1 12-3.6 3.4-7.3 6.7-11.2 9.7-.5.4-1.1.8-1.6 1.1-1.1.6-1.8.3-2.2-.9-.2-.5-.2-1-.3-1.5-.1-2.1-.1-4.3.3-6.4.6-3.6 1.6-7.2 2.6-10.8 1.6-5.5 3.3-10.9 4.9-16.4.3-1.1.6-2.1.6-3.3-.1-2.3-1.9-3.4-4-2.5-1.4.6-2.5 1.5-3.6 2.5-8.3 7.5-15.8 15.4-21.9 24.5-5.4 8.1-9.9 16.8-14.9 25.2-.1.3-.3.6-.4 1z" style="fill:#141414"></path></svg>
            <a href="/purchase" class="btn btn-l">Purchase ShopWP Pro 6.0 for $199 🚀</a>
        </div>

   </div>

</section>