<?php
/*
Plugin Name: EDD Variable Pricing Descriptions
Plugin URI: https://easydigitaldownloads.com/downloads/edd-variable-pricing-descriptions/
Description: Adds a description field for each variable pricing option
Version: 1.0.3
Author: Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
Text Domain: edd-vpd
Domain Path: languages
*/


/**
 * Internationalization
 * @since 1.0
 */
function edd_vpd_textdomain() {

	load_plugin_textdomain( 'edd-vpd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}
add_action( 'init', 'edd_vpd_textdomain' );


/**
 * Outputs the description
 * @since 1.0.2
 */
function edd_vpd_output_description( $key, $price, $download_id ) {

	$description = isset( $price['description'] ) ? $price['description'] : null;
	echo '<p class="edd-variable-pricing-desc">' . esc_html( $description ) . '</p>';

}
add_action( 'edd_after_price_option', 'edd_vpd_output_description', 10, 3 );


/**
 * Adds the table header
 *
 * @since 1.0
 */
function edd_vpd_download_price_table_head() { ?>

	<th><?php _e( 'Option Description', 'edd-vpd' ); ?></th>

<?php }
add_action( 'edd_download_price_table_head', 'edd_vpd_download_price_table_head' );


/**
 * Adds the table cell with description input field
 *
 * @since 1.0
 */
function edd_vpd_download_price_table_row( $post_id, $key, $args ) {
	$description = isset($args['description']) ? $args['description'] : null;
?>

	<td>
		<input type="text" class="edd_variable_prices_description" value="<?php echo esc_attr( $description ); ?>" placeholder="<?php _e( 'Option Description', 'edd-vpd' ); ?>" name="edd_variable_prices[<?php echo $key; ?>][description]" id="edd_variable_prices[<?php echo $key; ?>][description]" size="20" style="width:100%" />
	</td>

<?php }
add_action( 'edd_download_price_table_row', 'edd_vpd_download_price_table_row', 10, 3 );


/**
 * Add description field to edd_price_row_args
 *
 * @since 1.0
 */
function edd_vpd_price_row_args( $args, $value ) {

	$args['description'] = isset( $value['description'] ) ? $value['description'] : '';

	return $args;

}
add_filter( 'edd_price_row_args', 'edd_vpd_price_row_args', 10, 2 );
