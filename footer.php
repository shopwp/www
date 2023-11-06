<?php if (!is_page('checkout') && !is_page('purchase-confirmation')) { ?>
    
<footer id="footer">

    <div class="l-row l-sb l-contain footer-section">

        <div class="footer-branding">

            <div class="footer-newsletter">
                <p>Stay up-to-date with ShopWP while learning ecommerce skills from Andrew, creator of ShopWP.</p>
                <div id="root-newsletter-footer"></div>
            </div>

            <div class="shape"></div>
            
        </div>

        <div class="footer-nav">
            <div class="l-row">
                <div class="footer-nav-col">
                    <p>Company</p>
                    <ul>
                        <li>
                            <a href="/about">About</a>
                        </li>
                        <li>
                            <a href="/blog">Blog</a>
                        </li>
                        <li>
                            <a href="/brand-assets">Brand assets</a>
                        </li>
                        <li>
                            <a href="/refunds-and-payment-terms/">Refunds</a>
                        </li>
                        <li>
                            <a href="/refunds-and-payment-terms/">Privacy Policy</a>
                        </li>                                        
                    </ul>
                </div>            
                <div class="footer-nav-col">
                    <p>Plugin</p>
                    <ul>
                        <li>
                            <a href="/support">Support</a>
                        </li>
                        <li>
                            <a href="/features">Features</a>
                        </li>
                        <li>
                            <a href="/purchase">Pricing</a>
                        </li>
                        <li>
                            <a href="/examples">Examples</a>
                        </li>
                        <li>
                            <a href="/testimonials">Testimonials</a>
                        </li>
                        <li>
                            <a href="/changelog">Changelog</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-nav-col">
                    <p>Extensions</p>
                    <ul>
                        <li>
                            <a href="/extensions/recharge/">Recharge</a>
                        </li>
                        <li>
                            <a href="/extensions/elementor/">Elementor</a>
                        </li>
                        <li>
                            <a href="/extensions/translator/">Beaver Builder</a>
                        </li>
                        <li>
                            <a href="/extensions/translator/">Translator</a>
                        </li>
                        <li>
                            <a href="/extensions/webhooks/">Webhooks</a>
                        </li>
                        <li>
                            <a href="/extensions/yotpo/">Yotpo</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-nav-col">
                    <p>🔮 Support</p>
                    <ul>
                        <li>
                            <a href="https://docs.wpshop.io/" target="_blank">Documentation</a>
                        </li>
                        <li>
                            <a href="/slack">Slack channel</a>
                        </li>                    
                        <li>
                            <a href="/support">Contact</a>
                        </li>                    
                        <li>
                            <a href="/faq">FAQ</a>
                        </li>
                        <li>
                            <?php if (is_user_logged_in()) { ?>
                                <a href="/account">Go to account</a>
                            <?php } else { ?>
                                <a href="/login">Login</a>
                            <?php } ?>
                        </li>
                    </ul>
                </div>
            </div> 
        </div>
    
    </div>

    <div class="l-row l-sb l-contain l-align-items-flex-start footer-section">
        <div class="l-row footer-branding">
            <a href="/" class="logo">

                <svg width="120" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" id="Layer_1" x="0" y="0" style="enable-background:new 0 0 733 191" version="1.1" viewBox="0 0 733 191"><style>.st0{fill:#fff}</style><circle cx="91.6" cy="95.4" r="88.7" class="st0"/><path d="M78 54.8c2.7-6.8 8.6-11.5 15.4-11.5S106 48 108.8 54.8h7.2c-3.1-11.3-12-19.4-22.5-19.4S74.1 43.6 71 54.8h7zM138.4 65.9H46.6c-1.9 21.4-3.6 42.8-4.9 64.2.3 2.7 2.1 7.4 9.8 8H133.3c7.7-.6 9.5-5.2 9.8-8-1.1-21.5-2.8-42.8-4.7-64.2zm-18.4 38c-2.1 6.6-5.9 12.1-11.2 15.8-4.8 3.5-10.6 5.3-16.4 5.3h-.2c-5.7-.1-11.4-1.9-16-5.3-5.2-3.8-9-9.2-11.2-15.8l-.6-1.9h12l.3.7c2.8 6.9 8.9 11.3 15.9 11.3h.3c6.9-.1 12.9-4.4 15.6-11.3l.3-.7h12l-.8 1.9z"/><path d="M251.1 133.6c-9.9 0-21-3.3-30.4-10.7l8.6-13.2c7.7 5.6 15.7 8.5 22.4 8.5 5.8 0 8.5-2.1 8.5-5.3v-.3c0-4.4-6.9-5.8-14.7-8.2-9.9-2.9-21.2-7.5-21.2-21.3v-.3c0-14.4 11.6-22.5 25.9-22.5 9 0 18.8 3 26.5 8.2l-7.7 14c-7-4.1-14-6.6-19.2-6.6-4.9 0-7.4 2.1-7.4 4.9v.2c0 4 6.7 5.8 14.4 8.5 9.9 3.3 21.4 8.1 21.4 21v.3c0 15.7-11.7 22.8-27.1 22.8zM337.5 132.3V92.8c0-9.5-4.5-14.4-12.2-14.4s-12.6 4.9-12.6 14.4v39.5h-20.1V35.8h20.1v35.7c4.6-6 10.6-11.4 20.8-11.4 15.2 0 24.1 10.1 24.1 26.3v45.9h-20.1zM409.8 133.9c-22 0-38.2-16.3-38.2-36.6V97c0-20.4 16.4-36.9 38.5-36.9 22 0 38.2 16.3 38.2 36.6v.3c0 20.4-16.4 36.9-38.5 36.9zM428.5 97c0-10.4-7.5-19.6-18.6-19.6-11.5 0-18.4 8.9-18.4 19.3v.3c0 10.4 7.5 19.6 18.6 19.6 11.5 0 18.4-8.9 18.4-19.3V97zM505.1 133.6c-10.7 0-17.3-4.9-22.1-10.6v30.4h-20.1v-92H483v10.2c4.9-6.6 11.6-11.5 22.1-11.5 16.5 0 32.3 13 32.3 36.6v.3c-.1 23.7-15.5 36.6-32.3 36.6zm12.1-36.9c0-11.8-7.9-19.6-17.3-19.6s-17.2 7.8-17.2 19.6v.3c0 11.8 7.8 19.6 17.2 19.6 9.4 0 17.3-7.7 17.3-19.6v-.3zM623.5 132.9H618l-20.1-57.8-20.2 57.8h-5.6l-24.7-68h7.3l20.4 59.3 20.4-59.5h5.2l20.4 59.5 20.4-59.3h7l-25 68zM695.3 133.9c-13.5 0-22.2-7.7-27.8-16.1v35.7H661V64.9h6.5V80c5.8-8.9 14.4-16.7 27.8-16.7 16.3 0 32.8 13.1 32.8 35v.3c0 22-16.6 35.3-32.8 35.3zm25.8-35.3c0-17.7-12.3-29.1-26.5-29.1-14 0-27.5 11.8-27.5 29v.3c0 17.3 13.5 29 27.5 29 14.7 0 26.5-10.7 26.5-28.8v-.4z" class="st0"/></svg>

            </a>
            
            <p class="footer-logo-byline">Sell Shopify products on WordPress.</p>
        </div>
        <div class="l-col l-flex footer-misc">
            <div class="l-row l-sb l-contain footer-bottom">
                <div class="footer-copyright">
                    <small>&copy; <?php echo date("Y"); ?> ShopWP &mdash; Built with love in Austin, TX</small>
                </div>
                
                <code class="version"><a href="/changelog"><?= defined('SHOPWP_NEW_PLUGIN_VERSION') ? 'Latest version: ' . SHOPWP_NEW_PLUGIN_VERSION : 'View full changelog'; ?></a></code>
                <div class="footer-social l-row l-col-center">
                    <a href="https://www.youtube.com/c/WPShopify" class="footer-social-link" target="_blank" aria-label="Youtube channel link for ShopWP" rel="noreferrer">
                        <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="youtube" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"></path></svg>
                    </a>

                    <a href="https://twitter.com/andrewfromtx" class="footer-social-link" target="_blank" aria-label="Twitter link for ShopWP" rel="noreferrer">
                        <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="twitter" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"></path></svg>
                    </a>

                    <a href="https://github.com/shopwp" class="footer-social-link" target="_blank" aria-label="Github link for ShopWP" rel="noreferrer">
                        <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="github" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" data-fa-i2svg=""><path d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8zM97.2 352.9c-1.3 1-1 3.3.7 5.2 1.6 1.6 3.9 2.3 5.2 1 1.3-1 1-3.3-.7-5.2-1.6-1.6-3.9-2.3-5.2-1zm-10.8-8.1c-.7 1.3.3 2.9 2.3 3.9 1.6 1 3.6.7 4.3-.7.7-1.3-.3-2.9-2.3-3.9-2-.6-3.6-.3-4.3.7zm32.4 35.6c-1.6 1.3-1 4.3 1.3 6.2 2.3 2.3 5.2 2.6 6.5 1 1.3-1.3.7-4.3-1.3-6.2-2.2-2.3-5.2-2.6-6.5-1zm-11.4-14.7c-1.6 1-1.6 3.6 0 5.9 1.6 2.3 4.3 3.3 5.6 2.3 1.6-1.3 1.6-3.9 0-6.2-1.4-2.3-4-3.3-5.6-2z"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

</footer>

<?php } ?>

<?php wp_footer(); ?>

</body>
</html>