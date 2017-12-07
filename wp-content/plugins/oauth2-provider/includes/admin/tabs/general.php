<?php
/*
 * general.php
 * 
 * @author Justin Greer <justin@justin-greer.com
 * @copyright Justin Greer Interactive, LLC
 *
 * @package WP-Nightly
 */

$options = get_option( 'wo_license_information' );
?>
<table class="form-table">
	<tr>
		<th style="text-align:left;"><?php _e( 'WordPress Version ', 'wp-oauth' ); ?>:</th>
		<td>
			<?php global $wp_version;
			echo $wp_version; ?>
		</td>
	</tr>

	<tr>
		<th style="text-align:left;"><?php _e( 'PHP Version', 'wp-oauth' ); ?>
			(<?php echo PHP_VERSION; ?>):
		</th>
		<td>
			<?php echo version_compare( PHP_VERSION, '5.3.9' ) >= 0 ? " <span style='color:green;'>Ok</span>" : " <span style='color:red;'>Failed</span> - <small>Please upgrade PHP to 5.4 or greater.</small>"; ?>
		</td>
	</tr>

	<tr>
		<th style="text-align:left;"><?php _e( 'Running CGI', '' ); ?> :</th>
		<td>
			<?php echo substr( php_sapi_name(), 0, 3 ) != 'cgi' ? " <span style='color:green;'>NO (OK)</span>" : " <span style='color:orange;'>Notice</span> - <small>Header 'Authorization Basic' may not work as expected.</small>"; ?>
		</td>
	</tr>

	<tr>
		<th style="text-align:left;"><?php _e( 'Certificates Generated', 'wp-oauth' ); ?>:</th>
		<td>
			<?php echo ! wo_has_certificates() ? " <span style='color:red;'>Issues found with certificates.</span>" : "<span style='color:green;'>Certificates Found</span>" ?>
		</td>
	</tr>

	<tr>
		<th style="text-align:left;">Secure Server:</th>
		<td>
			<?php if ( false == wo_is_protocol_secure() ): ?>
				<span style="color:red;">NOT SECURE - <a href="https://www.thesslstore.com?aid=52913785"
				                                         title="Get A SSL Certificate">Get A SSL Certificate</a></span>
			<?php else: ?>
				<span style="color:green;">SECURE</span>
			<?php endif; ?>
		</td>
	</tr>

	<tr>
		<th style="text-align:left;"><?php _e( 'Running Windows OS', 'wp-oauth' ); ?>:</th>
		<td>
			<?php echo wo_os_is_win() ? " <span style='color:orange;'>Yes" : "<span style='color:green;'>No</span>" ?>
		</td>
	</tr>

	<tr>
		<th style="text-align:left;"><?php _e( 'Genuine', 'wp-oauth' ); ?>:</th>
		<td>
			<?php
			echo WOCHECKSUM == strtoupper( md5_file( WOABSPATH . '/includes/functions.php' ) ) ? "<span style='color:green;''>Yes</span>" : "<span style='color:orange;'>WARNING - THIS PLUGIN IS NOT GENUINE.</span>";
			?>
		</td>
	</tr>

</table>

