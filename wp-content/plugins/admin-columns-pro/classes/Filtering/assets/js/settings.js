jQuery( document ).ready( function( $ ) {

	$( document ).bind( 'column_init column_change column_add', function( e, column ) {
		var $column = $( column );

		var $label = $column.find( '.ac-setting-input_label' );
		var $filter_label = $column.find( '.ac-setting-input_filter_label' );

		if ( 0 === $filter_label.length ) {
			return;
		}

		var org_value = $label.val();
		var org_placeholder = $filter_label.attr( 'placeholder' );

		$label.bind( 'keyup change', function() {
			$filter_label.attr( 'placeholder', org_placeholder.replace( org_value, $( this ).val() ) );
		} );
	} );
} );