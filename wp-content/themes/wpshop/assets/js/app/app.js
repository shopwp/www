//  import '../../css/app/app.scss';

import { initPlugins } from './plugins/plugins'
import { initForms } from './forms/forms'
import { initMailinglist } from './mailinglist/mailinglist'
import { initAccount } from './account/account'
import { initMobile } from './mobile/mobile'
import { initDriftTracking, initDownloadTracking } from './analytics/analytics'
import { initFAQs } from './faqs/faqs'
;(function($) {
   $(function() {
      // $(document)
      //    .on('mouseenter', '.btn, .edd-submit, .edd_download_file_link, input[type="submit"]', function() {
      //       anime.remove($(this))
      //       anime({
      //          targets: $(this)[0],
      //          scale: [0.94],
      //          duration: 260,
      //          elasticity: 1000
      //       })
      //    })
      //    .on('mouseleave', '.btn, .edd-submit, .edd_download_file_link, input[type="submit"]', function() {
      //       anime({
      //          targets: $(this)[0],
      //          scale: [1],
      //          duration: 260,
      //          elasticity: 1000
      //       })
      //    })

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

      jQuery('iframe[src*="youtube"]')
         .parent()
         .fitVids()
   })
})(jQuery)
