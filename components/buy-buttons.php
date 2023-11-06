<?php 

$Products = ShopWP\Factories\Render\Products\Products_Factory::build();

?>
<section class="component component-buy-buttons">
    <div class="content l-contain-s">
        <h2>Display a simple buy button with ease. Customize and tweak them to fit your unique WordPress layout.</h2>
        <p>Use the built-in cart or send customers directly to checkout. Sell <a href="/extensions/recharge">subscription products</a> or single variant products. ShopWP lets you customize everything&mdash;perfect for making your products match your unique brand design.</p>
    </div>
    <div class="demo l-row l-row-center">
        
        <div class="buy-button-wrapper">
            <p>(Variant dropdowns style)</p>
            <?php $Products->buy_button([
                'title' => 'Super awesome t-shirt',
                'variant_style' => 'dropdown'
            ]); ?>
        </div>

        <div class="buy-button-wrapper">
            <p>(Variant buttons style)</p>
            <?php $Products->buy_button([
                'title' => 'Super awesome jacket',
                'variant_style' => 'buttons'
            ]); ?>
        </div>

        <div class="buy-button-wrapper">
            <p>(Subscription products)</a>
            <?php $Products->buy_button([
                'title' => 'Super awesome sunglasses',
                'variant_style' => 'buttons',
                'subscriptions' => true,
                'select_first_variant' => true
            ]); ?>
        </div>
        
    </div>
</section>