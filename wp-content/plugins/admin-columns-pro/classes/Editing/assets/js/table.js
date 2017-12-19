/**
 * AC variables. Defined in DOM.
 * @param AC {Object}
 * @param AC.table_id
 */
var AC;

/**
 * AC variables. Defined in DOM.
 * @param ACP_Editing {Object}
 * @param ACP_Editing.inline_edit
 * @param ACP_Editing.inline_edit.active {Boolean} Is editing on or off
 * @param ACP_Editing.i18n.select_author {String}
 * @param ACP_Editing.i18n.edit {String}
 * @param ACP_Editing.i18n.redo {String}
 * @param ACP_Editing.i18n.undo {String}
 * @param ACP_Editing.i18n.delete {String}
 * @param ACP_Editing.i18n.download {String}
 * @param ACP_Editing.i18n.errors.field_required {String}
 * @param ACP_Editing.i18n.errors.invalid_float {String}
 * @param ACP_Editing.i18n.errors.invalid_floats {String}
 * @param ACP_Editing.i18n.inline_edit {String}
 * @param ACP_Editing.i18n.change_images {String}
 * @param ACP_Editing.i18n.set_as_image {String}
 */
var ACP_Editing;

/**
 * AC variables. Defined in DOM.
 * @param {Object} ACP_Editing_Columns
 */
var ACP_Editing_Columns;

/**
 * AC variables. Defined in DOM.
 * @param ACP_Editing_Items {Object}
 * @param ACP_Editing_Items.columndata {Array}
 * @param ACP_Editing_Items.ID {Number}
 */
var ACP_Editing_Items;

/**
 * AC variables. Defined in DOM.
 * @param {Object} woocommerce_admin
 * @param woocommerce_admin.decimal_point {String}
 */
// TODO: move to WC addon
var woocommerce_admin;

/**
 * Format a list of options from a storage model for use in X-editable
 *
 * @since 1.0
 *
 * @param {Array} options List of options, can be nested (1 level max). Options have their key as the input value and their value as the input label. Parents have string 'label' and array 'options' of options.
 * @returns {Array} List of options with parents with 'text' and 'children' and options with 'id' and 'text'
 */
function cacie_options_format_editable( options ) {

	var foptions = [];

	if ( typeof options === "undefined" ) {
		return foptions;
	}

	for ( var i = 0; i < options.length; i++ ) {
		var parent;

		if ( typeof options[ i ].options !== 'undefined' ) {
			parent = {
				text : options[ i ].label,
				children : []
			};

			for ( var j in options[ i ].options ) {
				if ( options[ i ].options.hasOwnProperty( j ) ) {
					parent.children.push( {
						value : options[ i ].options[ j ].value,
						id : options[ i ].options[ j ].value,
						text : options[ i ].options[ j ].label
					} );
				}
			}
		}
		else {
			parent = {
				value : options[ i ].value,
				id : options[ i ].value,
				text : options[ i ].label
			};
		}

		foptions.push( parent );
	}

	return foptions;
}

function cacie_esc_regex( value ) {
	return value.replace( /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&" );
}

function cacie_is_float( value ) {

	var decimal_point_regex = '';

	// TODO: move to WC addon
	if ( woocommerce_admin.decimal_point ) {
		decimal_point_regex = '|' + cacie_esc_regex( woocommerce_admin.decimal_point );
	}

	var regex = new RegExp( "^[0-9]+((\." + decimal_point_regex + ")[0-9]+)?$" );

	return value.match( regex );

}

/**
 * Get query param from url
 *
 * @param param
 * @param url
 * @returns {*}
 */
function cacie_get_query_param_from_url( param, url ) {
	if ( !url ) {
		return null;
	}
	param = param.replace( /[\[\]]/g, "\\$&" );

	var regex = new RegExp( "[?&]" + param + "(=([^&#]*)|&|#|$)" ), results = regex.exec( url );

	if ( !results ) {
		return null;
	}

	if ( !results[ 2 ] ) {
		return '';
	}

	return decodeURIComponent( results[ 2 ].replace( /\+/g, " " ) );
}

/*
 * Init
 */
function cacie_init( $ ) {

	// List of xeditable elements for global reference
	window.xeditables = [];

	// this will allow non xeditable fields with click events to be disabled / enabled
	window.cacie_edit_enabled = 1;

	// Items table/container to apply editability on
	var list = $( AC.table_id );

	// Readability
	var columns = ACP_Editing_Columns;
	var items = ACP_Editing_Items;

	// Loop through columns
	for ( var column_name in columns ) {
		if ( !columns.hasOwnProperty( column_name ) ) {
			continue;
		}

		var column = columns[ column_name ];

		// Set column name
		column.column_name = column_name;

		// Make column editable
		if ( column.editable ) {
			if ( typeof column.editable.type === 'undefined' || column.editable.type === '' ) {
				continue;
			}

			var type = column.editable.type;
			var fn;

			// Function for editability of items in this column
			fn = 'cacie_edit_' + type;

			// Loop through items for current column
			$( '.column-' + column_name, list ).each( function() {

				if ( $( this ).hasClass( 'cacie-editable-container' ) ) {
					// in case we run init again
					return true;
				}
				// Get corresponding item for row
				var item;

				// Object ID
				var id = $( this ).parents( 'tr' ).find( '.check-column input' ).val();

				// When checkbox column is not available try to get the ID from <tr> attribute-id.
				if ( !id ) {
					var id_attr = $( this ).parents( 'tr' ).attr( 'id' );
					if ( id_attr ) {
						id = parseInt( id_attr.substr( id_attr.lastIndexOf( '-' ) + 1 ), 10 );
					}
				}

				// For MS Sites we can get the ID from the url.
				if ( !id ) {
					var href = $( this ).parents( 'tr' ).find( '.edit a' ).attr( 'href' );

					if ( href ) {
						id = cacie_get_query_param_from_url( 'id', href );
					}
				}

				if ( !( id in items ) ) {
					// Skip to the next element (equivalent to "continue" in a for-loop)
					return true;
				}

				item = items[ id ];

				// Current value
				var currentvalue;

				if ( column_name in item.columndata ) {
					currentvalue = item.columndata[ column_name ].revisions[ 0 ];
				}

				// Value must be defined and must no be a WP Error object
				if ( currentvalue == null || ( typeof currentvalue === 'undefined' ) || ( typeof currentvalue.errors !== 'undefined' ) ) {
					// Skip to the next element (equivalent to "continue" in a for-loop)
					return true;
				}

				// Save value to item
				if ( type == 'select2_dropdown' || type == 'select2_tags' ) {
					if ( currentvalue.length === 0 ) {
						currentvalue = [];
					}
				}

				item.columndata[ column_name ].value = currentvalue;

				// Add classes to table cell
				$( this ).addClass( 'cacie-editable-container cacie-editable-' + type );

				// Wrap the editable content for better control
				$( this ).wrapInner( '<span class="inner cacie-editable"></span>' );
				var el = $( this ).find( '.inner' );

				// set attributes
				el.cacie_handle_value( column, item );
				el.cacie_handle_actions( column, item );

				// Make editable
				el[ fn ]( column, item );
			} );
		}
	}
}

/*
 * Enable inline editing
 */
function cacie_enable( $ ) {
	$( '#cacie-toggle-edit' ).addClass( 'active' );

	if ( typeof window.xeditables === 'undefined' ) {
		cacie_init( $ );
	}

	// enable cacie when init has run once
	else {
		$( window.xeditables ).editable( 'enable' );
	}

	$( AC.table_id ).addClass( 'cacie-enabled' );
	window.cacie_edit_enabled = 1;
}

/*
 * Enable inline editing
 */
function cacie_disable( $ ) {
	$( '#cacie-toggle-edit' ).removeClass( 'active' );
	$( window.xeditables ).editable( 'disable' );
	window.cacie_edit_enabled = 0; // disable click events
	$( AC.table_id ).removeClass( 'cacie-enabled' );
}

/*
 * DOM ready
 */
jQuery( document ).ready( function( $ ) {
	$( '.wp-list-table tbody' ).on( 'updated', 'tr', function() {
		cacie_init( $ );
	} );

	// Columns and items are available
	if ( typeof ACP_Editing_Columns === 'undefined' || typeof ACP_Editing_Items === 'undefined' || ACP_Editing_Items == null ) {
		return;
	}

	// Any editable columns and items
	if ( ACP_Editing_Columns.length === 0 || ACP_Editing_Items.length === 0 ) {
		return;
	}

	// Add button
	$( '.tablenav.top .actions:last' ).append( '<a href="javascript:;" id="cacie-toggle-edit" class="cpac-edit add-new-h2">' + ACP_Editing.i18n.inline_edit + '</a>' );

	// Toggle Inline Edit
	$( '#cacie-toggle-edit' ).on( 'click', function( e ) {

		// disable
		if ( window.cacie_edit_enabled ) {
			cacie_disable( $ );
		}

		// enable
		else {
			cacie_enable( $ );
		}

		// store preference
		$.post( ajaxurl, {
			action : 'acp_editing_state_save',
			value : window.cacie_edit_enabled,
			list_screen : AC.list_screen,
			layout : AC.layout,
			_ajax_nonce : AC.ajax_nonce
		} );

		e.preventDefault();
	} );

	if ( ACP_Editing.inline_edit.active === true ) {
		cacie_enable( $ );
	}

	$( '.wp-list-table' ).on( 'click', '.cacie-editable a', function( e ) {
		e.stopPropagation();
		var $link = $( this );

		$link.parent().editable( 'destroy' );
		$link.parents( 'td:first' ).find( '.popover' ).remove();
		window.location.href = $link.attr( 'href' );
	} );

} );

(function( $ ) {

	$.fn.cacie_wrap_images = function() {
		$( this ).find( '.ac-image' ).each( function() {
			var id = $( this ).data( 'media-id' );
			if ( $( this ).parent( '.cacie-item' ).length > 0 ) {
				return;
			}

			var $el = $( '<div class="cacie-item"></div>' );

			$el.attr( 'data-cacie-id', id );

			$( this ).wrap( $el );
		} );
	};

	$.fn.cacie_show_message = function( message, type ) {

		if ( typeof type == 'undefined' ) {
			type = '';
		}
		else if ( type != 'info' && type != 'error' && type != 'success' ) {
			type = '';
		}

		var el_alert = $( '<div />' );

		el_alert.addClass( 'alert' );

		if ( type ) {
			el_alert.addClass( 'alert-' + type );
		}

		el_alert.append( '<button type="button" class="close" data-dismiss="alert">&times;</button>' );
		el_alert.append( message );

		$( this ).after( el_alert );
	};

	$.fn.cacie_after_save = function( column, item, newvalue ) {
		var el = $( this );

		if ( typeof newvalue != 'undefined' ) {
			el.cacie_set_value( column, item, newvalue );
		}

		el.closest( 'td' ).removeClass( 'processing' );
		el.cacie_handle_value( column, item );
		el.cacie_handle_actions( column, item );
		el.cacie_remove_ajax_loading();

		// hook for addons
		$( document ).trigger( 'cacie_after_save', column, item, newvalue );
	};

	$.fn.cacie_handle_value = function( column, item ) {
		var el = $( this );
		var currentvalue = el.cacie_get_value( column, item );
		var isempty = true;

		if ( currentvalue ) {
			isempty = false;
		}

		if ( column.editable.type == 'media' && currentvalue == 'false' ) {
			isempty = true;
		}

		if ( isempty ) {
			el.parents( 'td' ).removeClass( 'cacie-nonempty' );
			el.parents( 'td' ).addClass( 'cacie-empty' );
		}
		else {
			el.parents( 'td' ).addClass( 'cacie-nonempty' );
			el.parents( 'td' ).removeClass( 'cacie-empty' );
		}
	};

	$.fn.cacie_add_ajax_loading = function( column ) {
		var el = $( this );
		var editable_el = el.parents( 'td' ).find( '.cacie-editable' );

		// TODO: remove column references
		switch ( column.type ) {
			case 'title':
			case 'name':
			case 'username':
				if ( editable_el.siblings( '.post-state' ).length ) {
					editable_el = editable_el.siblings( '.post-state' ).eq( 0 );
				}
				break;
		}

		editable_el.after( '<div class="spinner cacie-ajax-loading" />' );
	};

	$.fn.cacie_remove_ajax_loading = function() {
		var el = $( this );

		el.parents( 'td' ).find( '.cacie-ajax-loading' ).remove();
	};

	$.fn.cacie_restore_revision = function( column, item, revision ) {
		var el = $( this );

		item.columndata[ column.column_name ].current_revision = revision;

		var revisions = ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].revisions;
		var current_revision = ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].current_revision;

		if ( 'is_xeditable' in column.editable && column.editable.is_xeditable ) {
			var target = el.cacie_get_target( column );

			el.cacie_add_ajax_loading( column );

			target.editable( 'setValue', revisions[ current_revision ], true );
			target.editable( 'submit' );
		}
		else {
			el.cacie_savecolumn( column, item, revisions[ current_revision ], false );
		}

		el.cacie_handle_actions( column, item );
	};

	$.fn.cacie_get_target = function( column ) {
		var el = $( this );

		if ( typeof column.editable.js != 'undefined' && typeof column.editable.js.selector != 'undefined' && el.find( column.editable.js.selector ).length ) {
			el = el.find( column.editable.js.selector );
		}

		return el;
	};

	/**
	 * Get value
	 *
	 * @since 1.0
	 */
	$.fn.cacie_get_value = function( column, item ) {
		return item.columndata[ column.column_name ].value;
	};

	/**
	 * Set value
	 *
	 * @since 1.0
	 */
	$.fn.cacie_set_value = function( column, item, newvalue ) {
		item.columndata[ column.column_name ].value = newvalue;
	};

	/**
	 * Edit type: Text
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_text = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'text',
			value : el.cacie_get_value( column, item )
		}, column, item );
	};

	/**
	 * Edit type: URL
	 *
	 * @since 3.6
	 */
	$.fn.cacie_edit_url = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'url',
			value : el.cacie_get_value( column, item )
		}, column, item );
	};

	/**
	 * Edit type: Float
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_float = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'text',
			value : el.cacie_get_value( column, item ),
			validate : function( value ) {
				if ( value && !cacie_is_float( value ) ) {
					return ACP_Editing.i18n.errors.invalid_float;
				}
			}
		}, column, item );
	};

	/**
	 * Edit type: Number
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_number = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'number',
			value : el.cacie_get_value( column, item )
		}, column, item );
	};

	/**
	 * Edit type: Password
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_password = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'password',
			value : el.cacie_get_value( column, item )
		}, column, item );
	};

	/**
	 * Edit type: Email
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_email = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'email',
			value : el.cacie_get_value( column, item )
		}, column, item );
	};

	/**
	 * Edit type: Checkbox list
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_checkboxlist = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'checklist'
		}, column, item );
	};

	/**
	 * Edit type: Textarea
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_textarea = function( column, item ) {

		var el = $( this );

		el.cacie_xeditable( {
			type : 'textarea',
			rows : 10,
			value : el.cacie_get_value( column, item )
		}, column, item );
	};

	/**
	 * Edit type: Select
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_select = function( column, item ) {

		var el = $( this );
		var value = el.cacie_get_value( column, item );
		var options = column.editable.options;

		el.cacie_xeditable( {
			type : 'select',
			value : value,
			source : cacie_options_format_editable( options )
		}, column, item );
	};

	/**
	 * Edit type: Select2 - Dropdown
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_select2_dropdown = function( column, item ) {

		var options = column.editable.options;
		var el = $( this );

		// populate options from object
		var defaults = {
			type : 'select2',
			showbuttons : true,
			source : cacie_options_format_editable( options ),
			select2 : {
				width : '230px'
			}
		};

		var column_editable = item.columndata[ column.column_name ];

		// populate options by ajax
		if ( typeof column.editable.ajax_populate !== 'undefined' && column.editable.ajax_populate ) {
			var _args = {
				source : '',
				showbuttons : false,
				select2 : {
					width : 230,
					minimumInputLength : 0,
					initSelection : function( element, callback ) {
						var data = [];

						if ( typeof column_editable.formattedvalue !== 'undefined' ) {
							for ( var id in column_editable.formattedvalue ) {
								data.push( {
									id : id,
									text : column_editable.formattedvalue[ id ]
								} );
							}
						}

						if ( data.length == 1 ) {
							callback( data[ 0 ] );
						}
						else if ( data.length > 0 ) {
							callback( data );
						}
					},
					ajax : {
						url : ajaxurl,
						dataType : 'json',
						quietMillis : 100,
						data : function( searchterm, page ) {
							return {
								action : 'acp_editing_get_options',
								layout : AC.layout,
								searchterm : searchterm,
								page : page,
								column : column.column_name,
								list_screen : AC.list_screen,
								item_id : item.ID,
								_ajax_nonce : AC.ajax_nonce
							};
						},
						results : function( response ) {
							if ( response.success ) {
								var more = response.data.more;

								if ( response.data.options == 0 ) {
									more = false
								}

								return {
									results : cacie_options_format_editable( response.data.options ),
									more : more
								};
							}

							// Close Select2 dropdown
							el.data( 'editable' ).input.$input.select2( 'close' );

							// Output error
							el.data( 'editable' ).container.$form.editableform( 'error', response.data );

							return { results : [] };
						}
					}
				}
			};

			defaults = $.extend( defaults, _args );
		}

		if ( typeof column.editable.multiple !== 'undefined' && column.editable.multiple ) {
			defaults.select2.multiple = true;
			defaults.showbuttons = true;
		}

		$( this ).cacie_xeditable( defaults, column, item );

		if ( typeof column.editable.ajax_populate !== 'undefined' && column.editable.ajax_populate ) {
			$( this ).on( 'shown', function() {
				var inp = el.data( 'editable' ).input.$input;

				inp.on( 'change', function() {
					column_editable.formattedvalue = [];
					var currentdata = inp.select2( 'data' );

					if ( typeof currentdata.id !== 'undefined' ) {
						column_editable.formattedvalue[ currentdata.id ] = currentdata.text;
					}
					else {
						for ( var i in currentdata ) {
							if ( currentdata.hasOwnProperty( i ) ) {
								column_editable.formattedvalue[ currentdata[ i ].id ] = currentdata[ i ].text;
							}
						}
					}
				} );

			} );
		}
	};

	/**
	 * Edit type: Select2 - Tags
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_select2_tags = function( column, item ) {

		var el = $( this );
		var options = column.editable.options;
		var value = el.cacie_get_value( column, item );

		// e.g. no terms available
		if ( 'false' === value || false === value ) {
			value = '';
		}

		el.cacie_xeditable( {
			type : 'select2',
			value : value,
			select2 : {
				width : 200,
				tags : cacie_options_format_editable( options )
			}
		}, column, item );
	};

	/**
	 * Edit type: togglable
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_togglable = function( column, item ) {

		var el = $( this );
		var options = column.editable.options;

		// Toggle on click
		$( this ).on( 'click', function() {
			if ( !window.cacie_edit_enabled || !options ) {
				return;
			}

			var currentvalue = el.cacie_get_value( column, item );
			var num_values = options.length;
			var current_index = 0;
			var newvalue;

			for ( var i in options ) {
				if ( options.hasOwnProperty( i ) && ( currentvalue == options[ i ].label ) ) {
					current_index = options[ i ].value;
					break;
				}
			}

			if ( typeof column.editable.required != 'undefined' && column.editable.required ) {
				if ( current_index !== 0 ) {
					el.cacie_show_message( ACP_Editing.i18n.errors.field_required );
					return;
				}
			}

			newvalue = options[ ( current_index + 1 ) % num_values ].label;

			// Save column
			el.cacie_savecolumn( column, item, newvalue, true );
		} );
	};

	$.fn.cacie_edit_media = function( column, item ) {
		var el = $( this );

		el.cacie_edit_attachment( column, item );
	};

	/**
	 * Edit type: media
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_attachment = function( column, item ) {

		var el = $( this );

		// Media upload
		el.on( 'click', function( e ) {
			e.preventDefault();

			if ( !window.cacie_edit_enabled ) {
				return;
			}

			var current_selection = el.cacie_get_value( column, item );

			if ( !$.isArray( current_selection ) ) {
				current_selection = [ current_selection ];
			}

			var args = {
				multiple : ( typeof column.editable.multiple != 'undefined' && column.editable.multiple ),
				title : ACP_Editing.i18n.media
			};

			if ( typeof column.editable.attachment != 'undefined' && typeof column.editable.attachment.library != 'undefined' ) {
				args.library = {};

				if ( typeof column.editable.attachment.library.uploaded_to_post != 'undefined' ) {
					args.library.uploadedTo = item.ID;
				}

				if ( typeof column.editable.attachment.library.type != 'undefined' ) {
					args.library.type = column.editable.attachment.library.type;
				}

				// Title

				if ( 'image' === column.editable.attachment.library.type ) {
					args.title = ACP_Editing.i18n.image;
				}

				if ( 'audio' === column.editable.attachment.library.type ) {
					args.title = ACP_Editing.i18n.audio;
				}
			}

			// Merge with column type-specific arguments
			if ( 'js' in column.editable ) {
				args = $.extend( args, column.editable.js );
			}

			// Init
			var uploader = wp.media( args );

			// Add current selection
			uploader.on( 'open', function() {
				var selection = uploader.state().get( 'selection' );

				current_selection.forEach( function( id ) {
					attachment = wp.media.attachment( id );
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
				} );
			} );

			// Store selection
			uploader.on( 'select', function() {
				var selection = uploader.state().get( 'selection' ).toJSON();
				var multiple = uploader.options.multiple;

				// multiple attachments
				var attachment_ids = [];

				for ( var k in selection ) {
					if ( selection.hasOwnProperty( k ) ) {
						var attachment = selection[ k ];
						attachment_ids.push( attachment.id );
					}
				}

				// Single attachment ( integer )
				if ( 1 === attachment_ids.length && !multiple ) {
					attachment_ids = attachment_ids[ 0 ];
				}

				// Save column
				el.cacie_savecolumn( column, item, attachment_ids );
			} );

			if ( typeof column.editable.attachment !== 'undefined' ) {
				if ( typeof column.editable.attachment.disable_select_current !== 'undefined' && column.editable.attachment.disable_select_current ) {
					uploader.on( 'ready', function() {
						setTimeout( function() {
						}, 1 );
					} );
				}
			}

			uploader.open();
		} );
	};

	/**
	 * Edit type: Date
	 *
	 * Uses bootstrap date picker; http://www.eyecon.ro/bootstrap-datepicker/
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_date = function( column, item ) {

		var el = $( this );
		var value = el.cacie_get_value( column, item );

		// convert yyyymmdd to yyyy-mm-dd
		if ( value ) {
			value = [ value.slice( 0, 4 ), '-', value.slice( 4 ) ].join( '' );
			value = [ value.slice( 0, 7 ), '-', value.slice( 7 ) ].join( '' );
		}

		el.attr( 'data-date', value );
		el.attr( 'data-date-format', 'yyyy-mm-dd' );

		el.bdatepicker().on( 'changeDate', function( ev ) {
			var new_date = new Date( ev.date );
			var yyyymmdd = new_date.yyyymmdd();

			el.cacie_savecolumn( column, item, yyyymmdd );
			el.bdatepicker( 'hide' );
		} );

		// Convert date object to yyyyddmm format
		// http://stackoverflow.com/questions/3066586/get-string-in-yyyymmdd-format-from-js-date-object
		Date.prototype.yyyymmdd = function() {
			var yyyy = this.getFullYear().toString();
			var mm = (this.getMonth() + 1).toString(); // getMonth() is zero-based
			var dd = this.getDate().toString();
			return yyyy + (mm[ 1 ] ? mm : "0" + mm[ 0 ]) + (dd[ 1 ] ? dd : "0" + dd[ 0 ]); // padding
		};
	};

	/**
	 * Edit type: taxonomy
	 *
	 * @since 1.0
	 */
	$.fn.cacie_edit_checklist = function( column, item ) {

		var el = $( this );
		var value = el.cacie_get_value( column, item );
		var options = column.editable.options;

		// e.g. no terms available
		if ( 'false' == value ) {
			value = '';
		}

		el.cacie_xeditable( {
			type : 'checklist',
			value : value,
			source : cacie_options_format_editable( options )
		}, column, item );
	};

	/**
	 * Make column editable via x-editable for a certain item
	 *
	 * @since 1.0
	 *
	 * @param {Object} args Arguments to be used for the x-editable call. Arguments that are passed will overwrite the default values for these arguments
	 * @param {Object} column Information about this specific editable column
	 * @param {Object} item The current item (row) that is being made editable (e.g. a post or user object)
	 */
	$.fn.cacie_xeditable = function( args, column, item ) {

		var el = $( this );

		var defaults = {
			url : ajaxurl,
			params : {
				action : 'acp_editing_column_save',
				list_screen : AC.list_screen,
				layout : AC.layout,
				pk : item.ID,
				column : column.column_name,
				_ajax_nonce : AC.ajax_nonce
			},
			pk : item.ID,
			value : $( this ).cacie_get_value( column, item ),
			placement : 'bottom',
			mode : 'popup', // or inline
			emptytext : ''
		};

		// Merge with edit type-specific arguments
		args = $.extend( defaults, args );

		// Merge with column type-specific arguments
		if ( typeof column.editable.js != 'undefined' ) {
			args = $.extend( args, column.editable.js );
		}

		// Placeholder
		if ( typeof column.editable.placeholder != 'undefined' ) {
			args.placeholder = column.editable.placeholder;
		}

		var htmlatts = [];

		// Max length
		if ( typeof column.editable.maxlength != 'undefined' ) {
			htmlatts.push( {
				key : 'maxlength',
				value : parseInt( column.editable.maxlength, 10 )
			} );
		}

		// Number range
		if ( typeof column.editable.range_min != 'undefined' ) {
			htmlatts.push( {
				key : 'min',
				value : parseFloat( column.editable.range_min, 10 )
			} );
		}

		if ( typeof column.editable.range_max != 'undefined' ) {
			htmlatts.push( {
				key : 'max',
				value : parseFloat( column.editable.range_max, 10 )
			} );
		}

		if ( typeof column.editable.range_step != 'undefined' ) {
			var step = 'any';

			if ( column.editable.range_step.length > 0 && column.editable.range_step != 'any' ) {
				step = parseFloat( column.editable.range_step, 10 );
			}

			htmlatts.push( {
				key : 'step',
				value : step
			} );
		}

		if ( htmlatts.length ) {
			var htmlatts_html = '';

			for ( var i in htmlatts ) {
				htmlatts_html += htmlatts[ i ].key + '="' + $( '<div />' ).text( htmlatts[ i ].value ).html() + '"';
			}

			switch ( column.editable.type ) {
				case 'number':
					args.tpl = '<input type="number" ' + htmlatts_html + '>';
					break;
				case 'text':
					args.tpl = '<input type="text" ' + htmlatts_html + '>';
					break;
				case 'textarea':
					args.tpl = '<textarea ' + htmlatts_html + '></textarea>';
					break;
			}
		}

		// Required
		if ( typeof column.editable.required != 'undefined' && column.editable.required ) {
			args.validate = function( value ) {
				var valid = true;

				switch ( column.editable.type ) {
					case 'select':
						if ( !value || value == 'null' ) {
							valid = false;
						}
						break;
					default:
						if ( !value.length ) {
							valid = false;
						}
						break;
				}

				if ( !valid ) {
					return ACP_Editing.i18n.errors.field_required;
				}
			};
		}

		var target = el.cacie_get_target( column );

		target.on( 'save', function( e, params ) {
			el.cacie_store_revision( column, item, params.newValue );
			el.cacie_after_save( column, item, params.newValue );
		} );

		// Display ajax returned value
		if ( typeof column.editable.display_ajax == 'undefined' || column.editable.display_ajax ) {
			args.display = function() {}; // should be left empty, do not remove
			args.success = function( response ) { // replace display with ajax value

				if ( response.success ) {
					el.cacie_replace_column_html( response.data, column.column_name );
				}

				el.cacie_after_save( column, item );

				if ( typeof response.data !== 'undefined' && typeof response.data.rawvalue !== 'undefined' ) {
					return { newValue : response.data.rawvalue };
				}
			};
		}
		else if ( typeof column.editable.js !== 'undefined' && typeof column.editable.js.selector !== 'undefined' ) {
			args.success = function() {
				el.cacie_after_save( column, item );
			}
		}
		else {
			args.success = function( response ) {
				el.cacie_after_save( column, item );

				if ( typeof response.data !== 'undefined' && typeof response.data.rawvalue !== 'undefined' ) {
					return { newValue : response.data.rawvalue };
				}
			};
		}

		// Add marker to editable that XEditable is in use
		column.editable.is_xeditable = true;

		// Reference to xeditables
		var xeditable = el;

		if ( typeof column.editable.js !== 'undefined' && typeof column.editable.js.selector !== 'undefined' ) {
			xeditable = el.find( column.editable.js.selector );
			el.removeClass( 'cacie-editable' );
			xeditable.addClass( 'cacie-editable' );
		}

		// Add for reference
		window.xeditables.push( xeditable );

		// XEditable
		el.editable( args );
	};

	/**
	 * Store Revision
	 *
	 * @since 1.0
	 *
	 * @param {Object} column Information about this specific editable column
	 * @param {Object} item The current item (row) that is being made editable (e.g. a post or user object)
	 * @param {String} value Revised value
	 */
	$.fn.cacie_store_revision = function( column, item, value ) {

		var revisions = ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].revisions;
		var current_revision = ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].current_revision;
		var num_deletes = revisions.length - current_revision - 1;

		for ( var i = 0; i < num_deletes; i++ ) {
			ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].revisions.pop();
		}

		ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].revisions.push( value );
		ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].current_revision++;

		$( this ).cacie_handle_actions( column, item );
	};

	/*
	 * Handle cell actions
	 */
	$.fn.cacie_handle_actions = function( column, item ) {
		var el, el_actions, el_undo, el_redo, el_clear, el_download, el_edit;

		el = $( this );
		el_actions = el.parent().find( '.cacie-cell-actions' );
		el.cacie_wrap_images();

		el_actions.remove();

		el.parent().find( '.cacie-edit, .cacie-undo, .cacie-redo, .cacie-clear' ).remove();

		el_actions = $( '<div class="cacie-cell-actions" />' );
		el_edit = $( '<a href="#" class="cacie-cell-action cacie-edit" title="' + ACP_Editing.i18n.edit + '" />' );
		el_undo = $( '<a href="#" class="cacie-cell-action cacie-undo" title="' + ACP_Editing.i18n.undo + '" />' );
		el_redo = $( '<a href="#" class="cacie-cell-action cacie-redo" title="' + ACP_Editing.i18n.redo + '" />' );
		el_clear = $( '<a href="#" class="cacie-cell-action cacie-clear" title="' + ACP_Editing.i18n.delete + '"/>' );

		el_undo.hide();
		el_redo.hide();
		el_clear.hide();

		el_actions.append( el_redo );
		el_actions.append( el_undo );

		if ( column.editable.clear_button ) {
			el_actions.prepend( el_clear );
		}

		// TODO: remove column references
		switch ( column.type ) {
			case 'title':
			case 'name':
			case 'username':
			case 'coupon_code':
				el_actions.prepend( el_edit );
				el.parents( 'td' ).find( column.editable.js.selector ).after( el_actions );
				//el.parents( 'td' ).find( '.row-actions a' ).click( function( e ) {
				//e.stopPropagation();
				//} );
				break;
			default:
				switch ( column.editable.type ) {
					case 'attachment':

						// add download button
						if ( el.find( '> a' ).length > 0 ) {
							el_download = $( '<a href="' + el.find( 'a' ).attr( 'href' ) + '" class="cacie-cell-action cacie-download" target="_blank" title="' + ACP_Editing.i18n.download + '"/>' );
							el_actions.prepend( el_download );
						}

						el_actions.prepend( el_edit );
						el.parents( 'td' ).find( '.cacie-editable' ).after( el_actions );
						break;
					case 'media':
						el_actions.prepend( el_edit );
						el_undo.before( '<div class="cacie-separator" />' );
						el_redo.before( '<div class="cacie-separator" />' );

						if ( typeof column.editable.multiple !== 'undefined' && column.editable.multiple ) {
							el.parents( 'td' ).addClass( 'cacie-multiple' );

							el_clear.remove();

							el.parents( 'td' ).find( '.cacie-item' ).each( function() {
								var el_item_actions = $( '<div class="cacie-item-actions" />' );
								var el_delete = $( '<a href="#" class="cacie-cell-action cacie-delete" title="' + ACP_Editing.i18n.delete + '"/>' );

								el_item_actions.append( el_delete );

								el_delete.on( 'click', ( function( column, item ) {
									return function() {
										var _item = $( this ).parents( '.cacie-item' );
										var val = el.cacie_get_value( column, item );

										if ( $.isArray( val ) ) {
											val = $.grep( val, function( value ) {
												return value != _item.attr( 'data-cacie-id' );
											} );
										}

										_item.fadeOut( 1000 );

										el.cacie_savecolumn( column, item, val );

										return false;
									};
								}( column, item ) ) );

								$( this ).append( el_item_actions );
							} );

							el.parents( 'td' ).find( '.cacie-editable' ).append( el_actions );
						}
						else {
							el.parents( 'td' ).find( '.cacie-editable' ).append( el_actions );
						}
						break;
					default:
						el_actions.prepend( el_edit );
						el.parents( 'td' ).find( '.cacie-editable' ).after( el_actions );
						break;
				}
				break;
		}

		// Edit: Click event
		el_edit.on( 'click', ( function( column ) {
			return function( e ) {
				e.preventDefault();
				e.stopPropagation();

				el.cacie_get_target( column ).trigger( 'click' );
			};
		}( column, item ) ) );

		// Undo: Click Event
		el_undo.on( 'click', ( function( column, item ) {
			return function( e ) {
				e.preventDefault();
				e.stopPropagation();

				el.cacie_restore_revision( column, item, item.columndata[ column.column_name ].current_revision - 1 );
			};
		}( column, item ) ) );

		// Redo: Click Event
		el_redo.on( 'click', ( function( column, item ) {
			return function( e ) {
				e.preventDefault();
				e.stopPropagation();

				el.cacie_restore_revision( column, item, item.columndata[ column.column_name ].current_revision + 1 );
			};
		}( column, item ) ) );

		// Clear: Click Event
		el_clear.on( 'click', ( function( column, item ) {
			return function( e ) {
				el.cacie_savecolumn( column, item, '' );
				e.stopPropagation();
			};
		}( column, item ) ) );

		var revisions = ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].revisions;
		var current_revision = ACP_Editing_Items[ item.ID ].columndata[ column.column_name ].current_revision;
		var current_value = revisions[ current_revision ];

		if ( current_revision < revisions.length - 1 ) {
			el_redo.show();
		}
		else {
			el_redo.hide();
		}

		if ( current_revision > 0 ) {
			el_undo.show();
		}
		else {
			el_undo.hide();
		}

		if ( current_value ) {
			el_clear.show();
		}
		else {
			el_clear.hide();
		}
	};

	/**
	 * Save a column by initiating an AJAX request, setting the item column HTML based on the return value from the AJAX call
	 *
	 * @since 1.0
	 *
	 * @param {Object} column Information about this specific editable column
	 * @param {Object} item The current item (row) that is being made editable (e.g. a post or user object)
	 * @param {String} newvalue New value for this item and column
	 * @param {Boolean} store_revision Optional. Whether to store the change as a revision
	 */
	$.fn.cacie_savecolumn = function( column, item, newvalue, store_revision ) {

		var el = $( this );

		// Update value for element
		el.cacie_set_value( column, item, newvalue );

		// Css transition
		el.closest( 'td' ).addClass( 'processing' );

		// Handle storing the revision
		store_revision = ( typeof store_revision == 'undefined' || store_revision );

		if ( store_revision ) {
			$( this ).cacie_store_revision( column, item, newvalue );
		}

		// Do AJAX request
		el.cacie_add_ajax_loading( column );

		$.post( ajaxurl, {
			action : 'acp_editing_column_save',
			list_screen : AC.list_screen,
			layout : AC.layout,
			column : column.column_name,
			pk : item.ID,
			value : newvalue,
			_ajax_nonce : AC.ajax_nonce
		}, function( response ) {
			if ( response.success ) {
				// update data even when empty, in case field is cleared
				el.cacie_replace_column_html( response.data, column.column_name );
			}

			el.cacie_after_save( column, item );
		}, 'json' );
	};

	/*
	 * Replace column HTML value
	 */
	$.fn.cacie_replace_column_html = function( data, column_name ) {
		var $td = $( this );
		var display_value = false;
		var $row_actions = $td.find( '.row-actions' );

		if ( data.cell_html ) {
			display_value = data.cell_html;
		}

		else if ( data.row_html ) {
			$row_actions = '';
			display_value = $( data.row_html ).find( 'td.' + column_name ).html();
		}

		// Add row-actions
		var $display_value = $( '<div>' ).html( display_value );

		// HTML
		$td.html( $display_value.append( $row_actions ).html() );
	};

}( jQuery ));

// xEditable Colorbox
( function( $ ) {
	"use strict";

	var ACP_Color_Input = function( options ) {
		this.init( 'color', options, ACP_Color_Input.defaults );
	};

	$.fn.editableutils.inherit( ACP_Color_Input, $.fn.editabletypes.abstractinput );

	$.extend( ACP_Color_Input.prototype, {

		render : function() {
			//var $container = this.$input;
			//var input_type = 'text';
			//$container.find( 'input' ).wpColorPicker();
		},

		value2input : function( value ) {
			var $container = this.$input;

			$container.find( 'input' ).val( value ).wpColorPicker();
		},

		input2value : function() {
			return this.$input.find( '.wp-color-picker' ).val();
		}
	} );

	var template = '';

	template += '<div class="single-input">';
	template += '<input type="text" name="ac_input_text">';
	template += '</div>';

	ACP_Color_Input.defaults = $.extend( {}, $.fn.editabletypes.abstractinput.defaults, {
		tpl : template
	} );

	$.fn.editabletypes.color = ACP_Color_Input;
}( window.jQuery ) );

jQuery.fn.cacie_edit_color = function( column, item ) {

	var el = jQuery( this );

	el.cacie_xeditable( {
		type : 'color',
		inputclass : column.editable.subtype,
		value : el.cacie_get_value( column, item )
	}, column, item );
};
