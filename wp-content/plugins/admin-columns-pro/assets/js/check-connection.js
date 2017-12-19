jQuery( document ).ready( function( $ ) {
	if ( $( '#toggle-ssl' ).length > 0 ) {
		jQuery.post( ajaxurl, {
			action : 'cpac_check_connection'
		}, function( data ) {
			if ( '1' !== jQuery.trim( data ) ) {
				$( '#toggle-ssl' ).show();
				$( '#licence_activation' ).hide();
			}
		} );
	}
} );