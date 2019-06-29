/*

Control center

*/
function initMobile($) {
   $('.icon-mobile-open, .icon-mobile-close').on('click', function() {
      $(this)
         .parent()
         .toggleClass('is-active')
      $('.nav-mobile').toggleClass('is-active')
   })
}

export { initMobile }
