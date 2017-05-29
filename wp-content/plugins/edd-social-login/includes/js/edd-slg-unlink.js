jQuery(document).ready( function($) {
	
	//Unlink Social media profile
	$(document).on('click','.edd-slg-social-unlink-profile',function(){
		var provider = $(this).attr('id');
		var data = { 
					action	 :'edd_slg_social_unlink_profile',
					provider :provider
				};
		
		//show loader
		jQuery('.edd-slg-login-loader').show();
		jQuery('.edd-social-login-profile').hide();
		
		jQuery.post( EDDSlgUnlink.ajaxurl,data,function(response){
			var result = jQuery.parseJSON( response );
			
			jQuery('.edd-slg-login-loader').hide();
			jQuery('.edd-social-login-profile').show();
			
			if(result.success =='1'){
				jQuery('.edd-social-login-profile').html(result.data);
				window.location.reload();
			}
			else if(result.failed=='1'){
				jQuery('.edd-slg-login-loader').hide();
				jQuery('.edd-social-login-profile').show();
			}
		});
	});
});