<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_LayoutScreen_Table {

	public function __construct() {
		add_action( 'ac/table/list_screen', array( $this, 'set_current_layout' ), 9 ); // Early priority
		add_action( 'admin_footer', array( $this, 'switcher' ) );
		add_action( 'ac/table_scripts', array( $this, 'table_scripts' ) );
		add_action( 'acp/filtering/form', array( $this, 'add_current_layout_form' ) );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function set_current_layout( $list_screen ) {
		$layouts = ACP()->layouts( $list_screen );

		// User switched layout
		if ( isset( $_GET['layout'] ) ) {
			if ( $layout = $layouts->get_layout_by_id( $_GET['layout'] ) ) {
				$layouts->set_user_preference( $layout );
			}
		}

		// Current user layouts
		if ( $layouts->get_layouts_for_current_user() ) {
			$layout = $layouts->get_user_preference();

			// when no longer available use the first user layout
			if ( ! $layout ) {
				$layout = $layouts->get_first_layout_for_current_user();
			}

			$list_screen->set_layout_id( $layout->get_id() );
		} // User doesn't have eligible layouts.. but the current (null) layout does exists, then the WP default columns are loaded
		else if ( $layouts->get_layout_by_id( null ) ) {
			// _wp_default_ does not exists therefor will load WP default
			$list_screen->set_layout_id( '_wp_default_' );
		}
	}

	/**
	 * Switcher on listing screen
	 */
	public function switcher() {
		$list_screen = AC()->table_screen()->get_current_list_screen();

		if ( ! $list_screen ) {
			return;
		}

		$layouts = ACP()->layouts( $list_screen )->get_layouts_for_current_user();

		if ( count( $layouts ) > 1 ) : ?>
            <form class="layout-switcher">
                <label for="column-view-selector" class="label">
					<?php _e( 'Column View', 'codepress-admin-columns' ); ?>
                </label>
                <span class="spinner"></span>
                <select id="column-view-selector" name="layout">
					<?php foreach ( $layouts as $layout ) : ?>
                        <option value="<?php echo add_query_arg( array( 'layout' => $layout->get_id() ), $list_screen->get_screen_link() ); ?>"<?php selected( $layout->get_id(), $list_screen->get_layout_id() ); ?>><?php echo esc_html( $layout->get_name() ); ?></option>
					<?php endforeach; ?>
                </select>
                <script type="text/javascript">
					jQuery( document ).ready( function( $ ) {
						$( '.layout-switcher' ).change( function() {
							var _select = $( this ).addClass( 'loading' ).find( 'select' ).attr( 'disabled', 1 );
							window.location = _select.val();
						} );
					} );
                </script>
            </form>
			<?php
		endif;
	}

	/**
	 * Loads scripts on the list screen
	 */
	public function table_scripts() {
		wp_enqueue_script( 'acp-layouts', ACP()->get_plugin_url() . 'assets/js/layouts-listings-screen.js', array( 'jquery' ), ACP()->get_version() );
		wp_enqueue_style( 'acp-layouts', ACP()->get_plugin_url() . 'assets/css/layouts-listings-screen' . AC()->minified() . '.css', array(), ACP()->get_version() );
	}

	/**
	 * Add current layout to filter form. Prevents loading the wrong layout, when changing layout in column settings.
	 *
	 * @param AC_Listscreen $list_screen
	 */
	public function add_current_layout_form( $list_screen ) {
		wp_nonce_field( 'select-layout', '_ac_nonce', false );
		?>
        <input type="hidden" name="layout" value="<?php echo esc_attr( $list_screen->get_layout_id() ); ?>">
		<?php
	}

}
