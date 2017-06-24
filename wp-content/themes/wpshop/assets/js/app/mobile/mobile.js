/*

Control center

*/
function initMobile($) {

  $('.icon-mobile').on('click', function() {
    $('.nav-mobile').toggleClass('is-active');
  });

}


export { initMobile }
