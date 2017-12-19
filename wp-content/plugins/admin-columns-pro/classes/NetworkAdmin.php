<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_NetworkAdmin {

	/**
	 * Hook suffix
	 *
	 * @var string
	 */
	private $hook_suffix;

	public function __construct() {
		add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
	}

	private function get_parent_slug() {
        return 'settings.php';
	}

	/**
	 * Add network settings page
	 *
	 * @since 3.6
	 */
	public function admin_menu() {
		$this->hook_suffix = add_submenu_page( $this->get_parent_slug(), __( 'Admin Columns Settings', 'codepress-admin-columns' ), __( 'Admin Columns', 'codepress-admin-columns' ), 'manage_admin_columns', AC_Admin::MENU_SLUG, array( $this, 'display' ) );
	}

	/**
	 * @return string
	 */
	public function get_hook_suffix() {
		return $this->hook_suffix;
	}

	/**
	 * @return string
	 */
	public function get_link() {
		return network_admin_url( add_query_arg( array( 'page' => AC_Admin::MENU_SLUG ), $this->get_parent_slug() ) );
	}

	/**
	 * Displays network settings page
	 *
	 * @since 3.6
	 */
	public function display() {

		if ( $groups = apply_filters( 'acp/network_settings/groups', array(), $this ) ) :
			?>
            <div id="cpac" class="wrap">
                <h1><?php echo __( 'Admin Columns', 'codepress-admin-columns' ); ?></h1>
                <table class="form-table ac-form-table settings">
                    <tbody>
					<?php
					foreach ( $groups as $id => $group ) :
						$defaults = array(
							'title'       => '',
							'description' => '',
						);
						$group = (object) array_merge( $defaults, $group );
						?>
                        <tr>
                            <th scope="row">
                                <h3><?php echo esc_html( $group->title ); ?></h3>
                                <p><?php echo $group->description; ?></p>
                            </th>
                            <td>
								<?php

								// Use this Hook to add additional fields to the group
								do_action( "acp/network_settings/group/" . $id );
								?>
                            </td>
                        </tr>
						<?php
					endforeach;
					?>
                    </tbody>
                </table>
            </div>
			<?php
		endif;
	}

}
