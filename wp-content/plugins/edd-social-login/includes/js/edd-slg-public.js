jQuery(document).ready( function($) {
	
	  if ( document.URL.indexOf('code=') != -1 && navigator.userAgent.match('CriOS') && EDDSlg.fbappid != '' )  {
		facebookTimer = setInterval(function() {
			if(typeof FB != "undefined")  {
				FB.getLoginStatus(function(response) {
			    	if ( response.status === 'connected' ) {
						var object = $( 'a.edd-slg-social-login-facebook' );
					  	edd_slg_social_connect( 'facebook', object );
					  	clearInterval( facebookTimer );
				  	}
			      }, true);
			 }
		} , 300);
	  }
	
	// login with facebook
	$( document ).on( 'click', 'a.edd-slg-social-login-facebook', function(){
		
		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.fberror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.fberrormsg );
			return false;
		} else if( navigator.userAgent.match('CriOS') && WOOSlg.fbappid != '' ) {
			FB.getLoginStatus(function(response) {
				if( response.status === 'connected' ) {
					woo_slg_social_connect( 'facebook', object );
				} else {
					var redirect_uri = document.location.href;
					var url = 'https://www.facebook.com/dialog/oauth?client_id='+WOOSlg.fbappid+'&redirect_uri='+redirect_uri+'&scope=email';
					var win =   window.open(url, '_parent' );
				}
			});
		} else {
				FB.login(function(response) {
					//alert(response.status);
				  if (response.status === 'connected') {
				  	//creat user to site
				  	edd_slg_social_connect( 'facebook', object );
				  }
				}, {scope:'email'});	
		}
	});
	
	// login with google+
	$( document ).on( 'click', 'a.edd-slg-social-login-googleplus', function(){
		
		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.gperror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.gperrormsg );
			return false;
		} else {
			
			var googleurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-gp-redirect-url').val();
			
			if(googleurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
				
			var googleLogin = window.open(googleurl, "google_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var gTimer = setInterval(function () { //set interval for executing the code to popup
				try {
					if (googleLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(gTimer);
						googleLogin.close();
						edd_slg_social_connect( 'googleplus', object );
					}
				} catch (e) {}
			}, 500);
		}
	});
	
	// login with linkedin
	$( document ).on( 'click', 'a.edd-slg-social-login-linkedin', function(){
	
		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.lierror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.lierrormsg );
			return false;
		} else {
		
			var linkedinurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-li-redirect-url').val();
			
			if(linkedinurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
			var linkedinLogin = window.open(linkedinurl, "linkedin", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var lTimer = setInterval(function () { //set interval for executing the code to popup
				try {
					if (linkedinLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(lTimer);
						linkedinLogin.close();
						edd_slg_social_connect( 'linkedin', object );
					}
				} catch (e) {}
			}, 300);
		}
		
	});
	
	// login with twitter
	$( document ).on( 'click', 'a.edd-slg-social-login-twitter', function(){
	
		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		//var redirect_url = $(this).parents('.edd-slg-social-container').find('.edd-slg-redirect-url').val();
		var parents = $(this).parents( 'div.edd-slg-social-container' );
		var appendurl = '';
		
		//check button is clicked form widget
		if( parents.hasClass('edd-slg-widget-content') ) {
			appendurl = '&container=widget';
		} 
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.twerror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.twerrormsg );
			return false;
		} else {
		
			var twitterurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-tw-redirect-url').val();
			
			if( twitterurl == '' ) {
				alert( EDDSlg.urlerror );
				return false;
			}
				
			var twLogin = window.open(twitterurl, "twitter_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var tTimer = setInterval(function () { //set interval for executing the code to popup
				try {
					/*if ( twLogin.location.hostname == window.location.hostname ) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(tTimer);
						twLogin.close();
						window.parent.location = EDDSlg.socialloginredirect+appendurl;
					}*/
					if ( twLogin.location.hostname == window.location.hostname ) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(tTimer);						
						twLogin.close();						
						if(EDDSlg.userid != ''){						
							edd_slg_social_connect( 'twitter', object );
						}
						else{
							window.parent.location = EDDSlg.socialloginredirect+appendurl;
						}
					}
				} catch (e) {}
			}, 300);
		}
		
	});
	
	// login with yahoo
	$( document ).on( 'click', 'a.edd-slg-social-login-yahoo', function(){

		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.yherror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.yherrormsg );
			return false;
		} else {
		
			var yahoourl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-yh-redirect-url').val();
			
			if(yahoourl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
			var yhLogin = window.open(yahoourl, "yahoo_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var yTimer = setInterval(function () { //set interval for executing the code to popup
				try {
					if (yhLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(yTimer);
						yhLogin.close();
						edd_slg_social_connect( 'yahoo', object );
					}
				} catch (e) {}
			}, 300);
		}
	});
	
	// login with foursquare
	$( document ).on( 'click', 'a.edd-slg-social-login-foursquare', function(){
	
		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.fserror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.fserrormsg );
			return false;
		} else {
		
			var foursquareurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-fs-redirect-url').val();
			
			if(foursquareurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
			var fsLogin = window.open(foursquareurl, "foursquare_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var fsTimer = setInterval(function () { //set interval for executing the code to popup
				try {
					if (fsLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(fsTimer);
						fsLogin.close();
						edd_slg_social_connect( 'foursquare', object );
					}
				} catch (e) {}
			}, 300);
		}
	});
	
	// login with windows live
	$( document ).on( 'click', 'a.edd-slg-social-login-windowslive', function(){
	
		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.wlerror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.wlerrormsg );
			return false;
		} else {
		
			var windowsliveurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-wl-redirect-url').val();
			
			if(windowsliveurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
			var wlLogin = window.open(windowsliveurl, "windowslive_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var wlTimer = setInterval(function () { //set interval for executing the code to popup
				try {
					if (wlLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(wlTimer);
						wlLogin.close();
						edd_slg_social_connect( 'windowslive', object );
					}
				} catch (e) {}
			}, 300);
		}
	});
	
	// login with VK.com
	$( document ).on( 'click', 'a.edd-slg-social-login-vk', function(){	
				
		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');		
		
		if( EDDSlg.vkerror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.vkerrormsg );
			return false;
		} else {
		
			var vkurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-vk-redirect-url').val();			
					
			if(vkurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
								
			var vkLogin = window.open(vkurl, "vk_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var vkTimer = setInterval(function () { //set interval for executing the code to popup
				try {					
					if (vkLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(vkTimer);
						vkLogin.close();						  
						edd_slg_social_connect( 'vk', object ); 
					}
				} catch (e) {}
			}, 300);
		}
	});
	
	// login with instagram
	$( document ).on( 'click', 'a.edd-slg-social-login-instagram', function(){

		var object = $(this);
		var errorel = $(this).parents('.edd-slg-social-container').find('.edd-slg-login-error');
		
		errorel.hide();
		errorel.html('');
		
		if( EDDSlg.insterror == '1' ) {
			errorel.show();
			errorel.html( EDDSlg.insterrormsg );
			return false;
		} else {
		
			var instagramurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-inst-redirect-url').val();
			
			if(instagramurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
			var instLogin = window.open(instagramurl, "instagram_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");			
			var instTimer = setInterval(function () { //set interval for executing the code to popup
				try {
					if (instLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(instTimer);
						instLogin.close();
						edd_slg_social_connect( 'instagram', object );
					}
				} catch (e) {}
			}, 300);
		}
	});
	
	//My Account Show Link Buttons "edd-slg-show-link"
	$( document ).on( 'click', '.edd-slg-show-link', function() {
		$( '.edd-slg-show-link' ).hide();
		$( '.edd-slg-profile-link-container' ).show();
	});
	
	
	// login with amazon
	$( document ).on( 'click', 'a.edd-slg-social-login-amazon', function() {
		
		var object	= $( this );
		var errorel	= $( this ).parents( '.edd-slg-social-container' ).find( '.edd-slg-login-error' );
		
		errorel.hide();
		errorel.html( '' );
		
		if( EDDSlg.amazonerror == '1' ) {
			
			errorel.show();
			errorel.html( EDDSlg.amazonerrormsg );
			return false;
			
		} else {		
			
			var amazonurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-amazon-redirect-url').val();			
			if(amazonurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
			var amazonLogin = window.open(amazonurl, "amazon_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");
			var amazonTimer = setInterval(function () { //set interval for executing the code to popup
				try { 
					if (amazonLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(amazonTimer);
						amazonLogin.close();
						edd_slg_social_connect( 'amazon', object );
					}
				} catch (e) {}
			}, 300);			
		}
	});
	
	
	// login with paypal
	$( document ).on( 'click', 'a.edd-slg-social-login-paypal', function() {
		
		var object	= $( this );
		var errorel	= $( this ).parents( '.edd-slg-social-container' ).find( '.edd-slg-login-error' );
		
		errorel.hide();
		errorel.html( '' );
		
		if( EDDSlg.paypalerror == '1' ) {
			
			errorel.show();
			errorel.html( EDDSlg.paypalerrormsg );
			return false;
			
		} else {		
			
			var paypalurl = $(this).closest('.edd-slg-social-container').find('.edd-slg-social-paypal-redirect-url').val();			
			if(paypalurl == '') {
				alert( EDDSlg.urlerror );
				return false;
			}
			var paypalLogin = window.open( paypalurl, "paypal_login", "scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");
			var paypalTimer = setInterval(function () { //set interval for executing the code to popup
				try { 
					if (paypalLogin.location.hostname == window.location.hostname) { //if login domain host name and window location hostname is equal then it will go ahead
						clearInterval(paypalTimer);
						paypalLogin.close();
						edd_slg_social_connect( 'paypal', object );
					}
				} catch (e) {}
			}, 300);			
		}
	});
	
	
	
});

// Social Connect Process
function edd_slg_social_connect( type, object ) {
	
	var data = { 
					action	:	'edd_slg_social_login',
					type	:	type
				};
			
	//show loader
	jQuery('.edd-slg-login-loader').show();
	jQuery('.edd-slg-social-wrap').hide();
	
	jQuery.post( EDDSlg.ajaxurl,data,function(response){
		
		
		// hide loader
		jQuery('.edd-slg-login-loader').hide();
		jQuery('.edd-slg-social-wrap').show();		
		var redirect_url = object.parents('.edd-slg-social-container').find('.edd-slg-redirect-url').val();
		
		if( response != '' ) {
			
			var result = jQuery.parseJSON( response );
			
			 if( redirect_url != '' ) {
				
				window.location = removeParam( redirect_url, 'code' );
				
			} else {
				
				//if user created successfully then reload the page
				var current_url  = window.location.href;
				current_url		= removeParam( current_url, 'code' );
				window.location = current_url;
			}
		}
	});
}

function removeParam( url, parameter ) { 
	 var urlparts= url.split('?');   
    if ( urlparts.length>=2 ) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }
        url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}