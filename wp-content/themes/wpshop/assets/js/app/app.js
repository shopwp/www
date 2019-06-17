//  import '../../css/app/app.scss';

import { onShopifyAuth } from './security/security'
import { initPlugins } from './plugins/plugins'
import { initForms } from './forms/forms'
import { initMailinglist } from './mailinglist/mailinglist'
import { initCheckout } from './checkout/checkout'
import { initAccount } from './account/account'
import { initMobile } from './mobile/mobile'
import { initDriftTracking, initDownloadTracking } from './analytics/analytics'
import { initFAQs } from './faqs/faqs'
import { showLatestBuildVersion } from './docs/docs'
;(function($) {
   $(function() {
      anime({
         targets: '.marquee-content h1',
         opacity: [0, 1],
         translateY: ['50px', '0px'],
         duration: 300,
         easing: 'spring(1, 120, 10, 0)',
         delay: 0
      })

      anime({
         targets: '.marquee-content .logo.wordpress-logo',
         opacity: [0, 1],
         translateX: ['-20px', '0px'],
         duration: 300,
         easing: 'spring(1, 120, 10, 0)',
         delay: 0
      })

      anime({
         targets: '.marquee-content .logo.shopify-logo',
         opacity: [0, 1],
         translateX: ['20px', '0px'],
         duration: 300,
         easing: 'spring(1, 120, 10, 0)',
         delay: 0
      })

      $(document)
         .on('mouseenter', '.btn, .edd-submit', function() {
            anime.remove($(this))
            anime({
               targets: $(this)[0],
               scale: [1.06],
               duration: 700,
               elasticity: 1000
            })
         })
         .on('mouseleave', '.btn, .edd-submit', function() {
            anime({
               targets: $(this)[0],
               scale: [1],
               duration: 700,
               elasticity: 1000
            })
         })

      initPlugins($)
      initForms($)
      initMailinglist($)
      // initCheckout($);
      initAccount($)
      initFAQs()

      // showLatestBuildVersion()

      initMobile($)

      initDriftTracking($)
      initDownloadTracking()
   })
})(jQuery)
