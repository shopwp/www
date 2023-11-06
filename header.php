<?php 

global $post;

$theme = empty($_COOKIE['shopwp_theme']) ? 'dark' : $_COOKIE['shopwp_theme'];

$seo_title = get_field('seo_title');
$seo_meta_description = get_field('seo_meta_description');
$type = 'WebSite';

if (is_page('faq')) {
   $type = 'FAQPage';
}

if (is_home()) {

	$page_for_posts = get_option( 'page_for_posts' );
	$seo_title = get_field('seo_title', $page_for_posts);
	$seo_meta_description = get_field('seo_meta_description', $page_for_posts);
}

?>
<!doctype html>

<html itemscope itemtype="https://schema.org/<?= $type; ?>" <?php language_attributes(); ?> class="theme-<?= $theme; ?>">

<head>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NWRL8QH');</script>
<!-- End Google Tag Manager -->

<?php if ( is_page('purchase-confirmation') ) {

	$purchaseData = shopwp_get_recent_receipt_data();

	if (!empty($purchaseData)) { ?>

		<script>

			var previousURL = document.referrer;
			var neededURL = '/checkout';

			if (previousURL.indexOf(neededURL) !== -1) {

				window.dataLayer = window.dataLayer || [];

				dataLayer.push({
					'transactionId': '<?php echo $purchaseData['transaction']->ID; ?>',
					'transactionAffiliation': '<?php echo $purchaseData['payment']['cart_details'][0]['name']; ?>',
					'transactionTotal': <?php echo $purchaseData['payment']['cart_details'][0]['subtotal']; ?>,
					'transactionTax': 0,
					'transactionShipping': 0,
					'transactionProducts': [{
					'sku': '<?php echo $purchaseData['transaction']->ID; ?>',
					'name': '<?php echo $purchaseData['payment']['cart_details'][0]['name']; ?>',
					'category': 'ShopWP License',
					'price': <?php echo $purchaseData['payment']['cart_details'][0]['price']; ?>,
					'quantity': 1
					}],
					'event': 'transactionComplete'
				});

			}

  		</script>

<?php } 

} ?>

<?php if (is_front_page()) { ?>
   <link rel="preload" as="image" href="https://cdn.shopify.com/s/files/1/0147/3639/2240/products/brooke-cagle-bAELYn2cQlo-unsplash_400x400_crop_center.jpg" />
<?php } ?>

	<!-- Facebook Meta Tags -->
	<meta property="og:url" content="https://wpshop.io/">
	<meta property="og:type" content="website">
	<meta property="og:title" content="ShopWP">
	<meta property="og:description" content="ShopWP is a premium WordPress plugin for selling Shopify products on WordPress.">
	<meta property="og:image" content="https://wpshop.io/wp-content/uploads/2023/10/og-image.jpg">

	<!-- Twitter Meta Tags -->
	<meta name="twitter:card" content="summary_large_image">
	<meta property="twitter:domain" content="wpshop.io">
	<meta property="twitter:url" content="https://wpshop.io/">
	<meta name="twitter:title" content="ShopWP">
	<meta name="twitter:description" content="ShopWP is a premium WordPress plugin for selling Shopify products on WordPress.">
	<meta name="twitter:image" content="https://wpshop.io/wp-content/uploads/2023/10/og-image.jpg">

	<meta itemprop="name" content="<?= get_the_title(); ?>"/>

	<?php if (is_front_page()) { ?>

		<link rel="preload" as="image" href="https://cdn.shopify.com/s/files/1/0147/3639/2240/products/brooke-cagle-bAELYn2cQlo-unsplash_400x400_crop_center.jpg" />

		<!-- Include the CSS & JS.. (This could be direct from the package or bundled) -->
		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() ?>/assets/vendor/lite-yt-embed.css" />

		<script src="<?php echo get_stylesheet_directory_uri() ?>/assets/vendor/lite-yt-embed.js"></script>

	<?php } ?>

	<?php if (is_front_page() || is_page('features')) { ?>
		<meta itemprop="version" content="<?= SHOPWP_NEW_PLUGIN_VERSION; ?>"/>	
	<?php } ?>
	
	<meta itemprop="description" content="<?= $seo_meta_description; ?>"/>
	<meta itemprop="url" content="<?= get_permalink(); ?>"/>
	<meta itemprop="genre" content="Digital Software"/>

	<meta name="ahrefs-site-verification" content="8d3d8d92bf0f5ed692d4bd2e4610c94a7579fd5fda2b3ca79ba87fef20a93fc9">

	<link rel="preconnect" href="https://cdn.shopify.com" crossorigin>
	<link rel="dns-prefetch" href="https://cdn.shopify.com">

	<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
	<link rel="dns-prefetch" href="https://fonts.googleapis.com">

	<link rel="preconnect" href="https://wpstest.myshopify.com" crossorigin>
	<link rel="dns-prefetch" href="https://wpstest.myshopify.com">

	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="dns-prefetch" href="https://fonts.gstatic.com">

	<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
	<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;700&family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glider-js@1/glider.min.css">
	
	<link media="(prefers-color-scheme: dark)" rel="apple-touch-icon" sizes="152x152" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/apple-touch-icon-dark.png">
	<link media="(prefers-color-scheme: dark)" rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-16x16-dark.png">
	<link media="(prefers-color-scheme: dark)" rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-32x32-dark.png">
	<link media="(prefers-color-scheme: dark)" rel="icon" type="image/png" sizes="180x180" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-180x180-dark.png">
	<link media="(prefers-color-scheme: dark)" rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-dark.ico">

	<link rel="manifest" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/site.webmanifest">
	<link rel="mask-icon" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="theme-color" content="#ffffff">

	<link media="(prefers-color-scheme: light)" rel="apple-touch-icon" sizes="152x152" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/apple-touch-icon-light.png">
	<link media="(prefers-color-scheme: light)" rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-16x16-light.png">
	<link media="(prefers-color-scheme: light)" rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-32x32-light.png">
	<link media="(prefers-color-scheme: light)" rel="icon" type="image/png" sizes="180x180" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-180x180-light.png">
	<link media="(prefers-color-scheme: light)" rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri() ?>/assets/imgs/favicon/favicon-light.ico">

	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php if (is_post_type_archive('download')) { ?>
		<meta name="description" content="ShopWP provides plugin extensions to add more functionality to your WordPress store. Sell subscription products with Recharge or add Elementor page builder support.">
		<title>ShopWP | Add extensions like Elementor to your store</title>
	<?php } else if (is_post_type_archive('faqs')) { ?>
		<meta name="description" content="These are the most frequently asked questions for the ShopWP WordPress plugin. Whether you're experiencing a syncing issue or have general questions.">
		<title>ShopWP | Question about WordPress Shopify integration</title>
	<?php } else { ?>
		<meta name="description" content="<?= empty($seo_meta_description) ? '' : $seo_meta_description; ?>">
		<title><?= empty($seo_title) ? 'ShopWP | ' . $post->post_title : $seo_title; ?></title>
	<?php } ?>

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NWRL8QH"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<?php if (is_front_page() || is_page('about') ) { ?>
	<div itemscope itemtype="http://schema.org/Organization">
		<meta itemprop="name" content="ShopWP" />
		<meta itemprop="logo" content="https://wpshop.io/wp-content/uploads/2023/09/logo-mark.png" />
		<meta itemprop="description" content="ShopWP is a premium WordPress plugin for selling Shopify products on WordPress. It allows for syncing data, displaying buy buttons, and building layouts." />
		<meta itemprop="url" content="https://wpshop.io/" />
		<meta itemprop="sameAs" content="https://twitter.com/andrewfromtx" />
		<meta itemprop="sameAs" content="https://www.youtube.com/channel/UCaEsEKxrizHpAJYbr2CllMg" />
		<meta itemprop="sameAs" content="https://github.com/arobbins/" />
	</div>
<?php } ?>