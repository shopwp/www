import { initPlugins } from './plugins/plugins'
import { initForms } from './forms/forms'
import { initMailinglist } from './mailinglist/mailinglist'
import { initAccount } from './account/account'
import { initMobile } from './mobile/mobile'
import { initDriftTracking, initDownloadTracking } from './analytics/analytics'
import { initFAQs } from './faqs/faqs'
;(function($) {
  $(function() {
    initPlugins($)
    initForms($)

    initAccount($)
    initFAQs()

    initMobile($)

    initDriftTracking($)
    initDownloadTracking()

    jQuery('iframe[src*="youtube"]')
      .parent()
      .fitVids()

    // grab an element
    var myElement = document.querySelector('.component-notice')

    if (myElement) {
      var headroom = new Headroom(myElement)
      headroom.init()
    }

    //  var subMenuTrigger = document.querySelector('.sub-nav-wrapper')
    //  var subMenuContent = document.querySelector('.nav-primary-sub')

    jQuery('.getting-started-trigger').each(function() {
      tippy(this, {
        content: document.getElementById('getting-started-wrapper').cloneNode(true),
        interactive: true,
        trigger: 'click',
        animation: 'shift-toward',
        theme: 'light-border',
        arrow: true,
        arrowType: 'round',
        distance: 7,
        placement: 'right',
        maxWidth: 450,
        duration: [280, 0],
        moveTransition: 'transform 0.2s ease-out',
        offset: [0, 20],
        onCreate: function(instance) {
          var $form = jQuery(instance.popper).find('.mailinglist-form')
          initMailinglist($form)
        }
      })
    })

    jQuery('.testimonials').masonry({
      itemSelector: '.grid-item',
      columnWidth: '.grid-sizer',
      percentPosition: true,
      gutter: 20
    })
  })
})(jQuery)
