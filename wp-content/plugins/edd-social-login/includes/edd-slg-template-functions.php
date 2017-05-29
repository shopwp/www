<?php
/**
 * Templates Functions
 *
 * Handles to manage templates of plugin
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 **/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Returns the path to the Review Engine templates directory
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */
function edd_slg_get_templates_dir() {
	
	return EDD_SLG_DIR . '/includes/templates/';
	
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 * 
 */
function edd_slg_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	
	if ( ! $template_path ) $template_path = EDD_SLG_BASENAME . '/'; //edd_slg_get_templates_dir();
	if ( ! $default_path ) $default_path = edd_slg_get_templates_dir();
	
	// Look within passed path within the theme - this is priority
	
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);
	
	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters('edd_slg_locate_template', $template, $template_name, $template_path);
}

/**
 * Get other templates (e.g. fbre attributes) passing attributes and including the file.
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 * 
 */
function edd_slg_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	
	if ( $args && is_array($args) )
		extract( $args );

	$located = edd_slg_locate_template( $template_name, $template_path, $default_path );
	
	//do_action( 'edd_slg_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	//do_action( 'edd_slg_after_template_part', $template_name, $template_path, $located, $args );
}

?>