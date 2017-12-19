<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Editing instance
 *
 * @since 4.0
 * @return ACP_Editing_Addon
 */
function acp_editing() {
	return ACP()->editing();
}

/**
 * @return ACP_Editing_Helper
 */
function acp_editing_helper() {
	return ACP()->editing()->helper();
}

/**
 * Filtering instance
 *
 * @since 4.0
 * @return ACP_Filtering_Addon
 */
function acp_filtering() {
	return ACP()->filtering();
}

/**
 * Sorting instance
 *
 * @since 4.0
 * @return ACP_Sorting_Addon
 */
function acp_sorting() {
	return ACP()->sorting();
}

/**
 * @since 4.0
 * @return bool True when a minimum version of Admin Columns Pro plugin is activated.
 */
function acp_is_version_gte( $version ) {
	return version_compare( ACP()->get_version(), $version, '>=' );
}

/**
 * @see ac_register_columns
 * @deprecated 4.0
 */
function cpac_set_storage_model_columns( $list_screen_key, $column_data ) {
	_deprecated_function( __METHOD__, '4.0', 'ac_register_columns()' );

	ac_register_columns( $list_screen_key, $column_data );
}
