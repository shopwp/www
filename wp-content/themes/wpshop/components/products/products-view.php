<section class="component component-products">
  <header class="component-products-header">
    <h1>Choose your license</h1>
    <p>(you can always upgrade later)</p>
  </header>
  <ul class="products l-row l-row-justify">
    <?php foreach ($productVariant as $key => $variant) { ?>
      <li class="product card l-col l-box l-box-3">
        <h2 class="product-name"><?php print_r($variant['name']); ?></h2>
        <h3 class="product-price"><?php print_r($variant['amount']); ?></h3>
        <p><?php print_r($variant['description']); ?></p>
      </li>
    <?php } ?>
  </ul>
  <div class="component-products-purchase l-row l-row-center">
    <a href="#" class="btn btn-l">Purchase</a>
  </div>
</section>
