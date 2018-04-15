function getDocsToken() {

  var options = {
    method: 'POST',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'wps_get_access_token'
    }
  };
  

  return jQuery.ajax(options);

};

export {
  getDocsToken
}
