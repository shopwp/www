jQuery( document ).ready( function( $ ) {

	$( '#acp_overflow_list_screen_table' ).on( 'click', function() {

		if ( $( this ).is( ':checked' ) ) {
			$( 'body' ).addClass( 'acp-overflow-table' );
		} else {
			$( 'body' ).removeClass( 'acp-overflow-table' );
		}

		$.post( ajaxurl, {
			action : 'acp_update_table_option_overflow',
			value : $( this ).is( ':checked' ),
			layout : AC.layout,
			list_screen : AC.list_screen,
			_ajax_nonce : AC.ajax_nonce
		} );
	} );

} );