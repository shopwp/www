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
      initMailinglist($)

      initAccount($)
      initFAQs()

      initMobile($)

      initDriftTracking($)
      initDownloadTracking()

      jQuery('iframe[src*="youtube"]')
         .parent()
         .fitVids()
   })
})(jQuery)
