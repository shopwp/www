<?php

define( 'SHORTINIT', true );

require(dirname(__FILE__) . '/../../../../../wp-load.php');

// Always equal the new version number that gets passed in. E.g.: 5.1.4
$new_version = $argv[1];

// new changelog entry
$new_changelog_entry = base64_decode($argv[2]);

// Grab the current database values
$version = (array) $wpdb->get_results("select * from $wpdb->postmeta where meta_key = '_edd_sl_version'")[0];
$changelogEDD = $wpdb->get_results("select * from $wpdb->postmeta where meta_key = '_edd_sl_changelog'");
$downloadpath = (array) $wpdb->get_results("select * from $wpdb->postmeta where meta_key = 'edd_download_files'")[0];
$domain = (array) $wpdb->get_results("select * from $wpdb->options where option_name = 'home'")[0];

$changelogProContent = $changelogEDD;

// Set old version to new version
$version['meta_value'] = $new_version;


/*

$productInfo represents the meta_value of 'edd_download_files'. We need to
unserialize into a real array so that we can add to it

*/
$productInfo = array_values( maybe_unserialize($downloadpath['meta_value']) )[0];

/*

Now we update the 'file' with the updated download path for the new release

E.g. https://wpshop.io/releases/5.1.2/_pro/shopwp-pro.zip

*/
$productInfo['file'] = $domain['option_value'] . '/releases/' . $new_version . '/_pro/shopwp-pro.zip';

// Serialize everything again __and__ wrap it inside an array
$downloadpath['meta_value'] = maybe_serialize([$productInfo]);

// Updating the EDD changelog
$changelogEDD = (array) $changelogEDD[0];

$changelogEDD['meta_value'] = $new_changelog_entry . $changelogEDD['meta_value'];

/*

Performs the updates

*/
$versionResults = $wpdb->update($wpdb->postmeta, $version, ['meta_key' => '_edd_sl_version', 'post_id' => 35] );
$downloadResults = $wpdb->update($wpdb->postmeta, $downloadpath, ['meta_key' => 'edd_download_files', 'post_id' => 35] );
$changelogResults = $wpdb->update($wpdb->postmeta, $changelogEDD, ['meta_key' => '_edd_sl_changelog', 'post_id' => 35] );