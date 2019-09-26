import { initPlugins } from './plugins/plugins'
import { initForms } from './forms/forms'
import { initMailinglist } from './mailinglist/mailinglist'
import { initAccount } from './account/account'
import { initMobile } from './mobile/mobile'
import { initDriftTracking, initDownloadTracking } from './analytics/analytics'
import { initFAQs } from './faqs/faqs'
import tippy from 'tippy.js'
;(function($) {
   $(function() {
      initPlugins($)
      initForms($)
      initMailinglist($)

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
      console.log('myElement', myElement)

      if (myElement) {
         var headroom = new Headroom(myElement)
         headroom.init()
      }

      var subMenuTrigger = document.querySelector('.sub-nav-wrapper')
      var subMenuContent = document.querySelector('.nav-primary-sub')

      tippy(subMenuTrigger, {
         animateFill: false,
         content: subMenuContent,
         interactive: true,
         animation: 'shift-away',
         theme: 'wpshopify-popover',
         arrow: true,
         arrowType: 'round',
         distance: 7
      })
   })
})(jQuery)
