<?php

define( 'SHORTINIT', true );

require(dirname(__FILE__) . '/../../../../../wp/wp-load.php');


/*

Grab the current database values

*/
$version = (array) $wpdb->get_results("select * from $wpdb->postmeta where meta_key = '_edd_sl_version'")[0];
$changelogEDD = $wpdb->get_results("select * from $wpdb->postmeta where meta_key = '_edd_sl_changelog'");
$downloadpath = (array) $wpdb->get_results("select * from $wpdb->postmeta where meta_key = 'edd_download_files'")[0];
$domain = (array) $wpdb->get_results("select * from $wpdb->options where option_name = 'home'")[0];
$changelogPro = (array) $wpdb->get_results("select * from $wpdb->posts where post_type = 'docs' and post_title = 'Changelogs'")[0];

$changelogProID = $changelogPro['ID'];
$changelogProContent = (array) $wpdb->get_results("select * from $wpdb->postmeta where post_id = " . $changelogProID . " and meta_key = 'changelog_pro'")[0];

/*

$argv[1] will always equal the new version number that gets passed in

*/
$version['meta_value'] = $argv[1];
$tier = $argv[2];


/*

$productInfo represents the meta_value of 'edd_download_files'. We need to
unserialize into a real array so that we can add to it

*/
$productInfo = array_values( maybe_unserialize($downloadpath['meta_value']) )[0];


/*

Now we update the 'file' with the updated download path for the new release

*/
$productInfo['file'] = $domain['option_value'] . '/pro/releases/' . $argv[1] . '/wp-shopify-pro.zip';


/*

We need to serialize everything again __and__ wrap it inside an array

*/
$downloadpath['meta_value'] = maybe_serialize([$productInfo]);


/*

Updating the EDD changelog

*/
$changelogEDD = (array) $changelogEDD[0];
$changelogEDD['meta_value'] = $changelogProContent['meta_value'];


/*

Performs the updates

*/
$versionResults = $wpdb->update($wpdb->postmeta, $version, ['meta_key' => '_edd_sl_version', 'post_id' => 35] );
$downloadResults = $wpdb->update($wpdb->postmeta, $downloadpath, ['meta_key' => 'edd_download_files', 'post_id' => 35] );
$changelogResults = $wpdb->update($wpdb->postmeta, $changelogEDD, ['meta_key' => '_edd_sl_changelog', 'post_id' => 35] );


$themeOptionsVersionNumberUpdateResults = $wpdb->update($wpdb->options, ['option_value' => $version['meta_value']], ['option_name' => 'options_theme_latest_' . $tier . '_version' ] );

if ($themeOptionsVersionNumberUpdateResults === false) {
  error_log('WP Shopify build error: Unabled to update theme options version number');
}
