//window.fbAsyncInit = function() {
    // init the FB JS SDK
    FB.init({
      appId      : EDDSlgFbInit.app_id, // App ID from the App Dashboard
      status     : true, // check the login status upon init?
      cookie     : true, // set sessions cookies to allow your server to access the session?
      xfbml      : true,  // parse XFBML tags on this page?
      oauth		 : true
    });

    // Additional initialization code such as adding Event Listeners goes here

//  };