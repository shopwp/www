<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'widgets_init', 'edd_slg_login_buttons_widget' );

/**
 * Register the Login Buttons Listing
 * 
 * Handles to register a widget
 * for showing active login buttons
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.1.0
 */
function edd_slg_login_buttons_widget() {
	register_widget( 'Edd_Slg_Login_Buttons' );
}

/**
 * Easy Digital Downloads WP Social Deals Widget Class. 
 *
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update for displaying submitted reviews.
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.1.0
 */ 
class Edd_Slg_Login_Buttons extends WP_Widget {

	var $model,$render;

	/**
	 * Widget setup.
	 */
	function __construct() {

		global $edd_slg_model, $edd_slg_render;

		$this->model = $edd_slg_model;
		$this->render = $edd_slg_render;

		/* Widget settings. */
		$widget_ops = array( 'classname' => 'edd-slg-login-buttons', 'description' => __( 'A social login widget.', 'eddslg' ) );

		/* Create the widget. */		
		parent::__construct( 'edd-slg-login-buttons', __( 'Easy Digital Downloads - Social Login', 'eddslg' ), $widget_ops );
	}
	
	/**
	 * Outputs the content of the widget
	 * 
	 * Handles to show output of widget 
	 * at front side sidebar
	 * 
	 * @package Easy Digital Downloads - Social Login
 	 * @since 1.1.0
	 * 
	 */
	function widget( $args, $instance ) {
	
		global $wpdb, $post, $edd_options;
		
		extract( $args );
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		if( !is_user_logged_in() && edd_slg_check_social_enable() ) {
			
	    	echo $before_widget;
	    
	    	// get redirect url from settings 
			$defaulturl = isset( $edd_options['edd_slg_redirect_url'] ) && !empty( $edd_options['edd_slg_redirect_url'] ) 
								? $edd_options['edd_slg_redirect_url'] : edd_slg_get_current_page_url();
			
			//session create for redirect url 
			EDD()->session->set( 'edd_slg_stcd_redirect_url_widget', $defaulturl );
			
	    	echo '<div class="edd-slg-social-container edd-slg-widget-content">';
	    	
	        echo $before_title . $title . $after_title;
	    	
	        $this->render->edd_slg_social_login_inner_buttons();
	        
			//end container
	    	echo '</div><!--.edd-slg-widget-content-->';
	    
			echo $after_widget;
		}
    }
	
	/**
	 * Updates the widget control options for the particular instance of the widget
	 *
	 * Handles to update widget data
	 * 
	 * @package Easy Digital Downloads - Social Login
 	 * @since 1.1.0 
	 *
	 */
	function update( $new_instance, $old_instance ) {
	
        $instance = $old_instance; 
		
		/* Set the instance to the new instance. */
		$instance = $new_instance;
		
		/* Input fields */
        $instance['title'] = strip_tags( $new_instance['title'] );
		
        return $instance;
		
    }
	
	/**
	 * Displays the widget form in the admin panel
	 * 
	 * Handles to show widget settings at backend
	 * 
	 * @package Easy Digital Downloads - Social Login
 	 * @since 1.1.0
	 * 
	 */
	function form( $instance ) {
	
		global $edd_options;
		
		// login heading from setting page
		$login_heading = isset( $edd_options['edd_slg_login_heading'] ) ? $edd_options['edd_slg_login_heading'] : 'Prefer to Login with Social Media';
				
		$defaults = array( 'title' => __($login_heading, 'eddslg') );
		
        $instance = wp_parse_args( (array) $instance, $defaults );
		
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'eddslg'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>

		<?php
	}
}