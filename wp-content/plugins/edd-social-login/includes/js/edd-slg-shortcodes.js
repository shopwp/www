// JavaScript Document
jQuery(document).ready(function($) {
	var eddslged;
	var eddslgurl;
	//add the button to tinymce editor
	(function() {
	    tinymce.create('tinymce.plugins.edd_social_login', {
	    	
	    	init : function(ed, url) {
				
				ed.addButton('edd_social_login', {
					
					title : 'EDD Social Login',
					image : url+'/shortcode-icon.png',
					onclick : function() {
						
	                    $( '#edd_slg_redirect_url' ).val( '' );
	                    $( '#edd_slg_show_on_page' ).attr( 'checked', false );
						
						var popupcontent = $( '.edd-slg-popup-content' );
						popupcontent.fadeIn();
						$( '.edd-slg-popup-overlay' ).fadeIn();
	 				}
				});
	    	},
	        createControl : function(n, cm) {
				return null;
			}
		});
		tinymce.PluginManager.add('edd_social_login', tinymce.plugins.edd_social_login);
	
	})();
	
	//close popup window
	$( document ).on( 'click', '.edd-slg-close-button, .edd-slg-popup-overlay', function(){
		
		$( '.edd-slg-popup-overlay' ).fadeOut();
        $( '.edd-slg-popup-content' ).fadeOut();
        
	});
	
	$( document ).on( 'click', '#edd_slg_insert_shortcode', function(){
		
		var eddslgshortcode = $('#edd_slg_shortcodes').val();
		var eddslgshortcodestr = '';
			
			if(eddslgshortcode != '') {
				
				eddSlgSwitchDefaultEditorVisual();
				
				switch(eddslgshortcode) {
					
					case 'edd_social_login'	:
								var title 			=	$( '#edd_slg_title' ).val();
								var redirect_url 	=	$( '#edd_slg_redirect_url' ).val();
								var showonpage		=	$( '#edd_slg_show_on_page'); 
								
								eddslgshortcodestr	+= '['+eddslgshortcode;
								if(title != '') {
									eddslgshortcodestr	+= ' title="'+title+'"';
								}
								if( showonpage.is(':checked') ) {
									eddslgshortcodestr	+= ' showonpage="true"';
								}
								if(redirect_url != '') {
									eddslgshortcodestr	+= ' redirect_url="'+redirect_url+'"';
								}
								eddslgshortcodestr	+= '][/'+eddslgshortcode+']';
								break;
					default:
									break;
				}
			 	
			 	 //send_to_editor(str);
		        //tinymce.get('content').execCommand('mceInsertContent',false, eddslgshortcodestr);
		        window.send_to_editor( eddslgshortcodestr );
		  		jQuery('.edd-slg-popup-overlay').fadeOut();
				jQuery('.edd-slg-popup-content').fadeOut();
		}
		
	});
	
});

//switch wordpress editor to visual mode
function eddSlgSwitchDefaultEditorVisual() {
	if (jQuery('#content').hasClass('html-active')) {
		switchEditors.go(editor, 'tinymce');
	}
}