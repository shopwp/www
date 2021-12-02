function initMobile($) {
  $('.icon-mobile-open, .icon-mobile-close').on('click', function () {
    $(this).closest('body').toggleClass('is-mobile-active');
    $('.nav-mobile').toggleClass('fadeInDownBig');
  });
}

export { initMobile };
