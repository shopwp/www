import '../../css/app/app.scss';

import { onShopifyAuth } from './security/security';
import { initPlugins } from './plugins/plugins';
import { initForms } from './forms/forms';
import { initMailinglist } from './mailinglist/mailinglist';
import { initCheckout } from './checkout/checkout';
import { initAccount } from './account/account';
import { initMobile } from './mobile/mobile';
import { initDriftTracking, initDownloadTracking } from './analytics/analytics';
import { initFAQs } from './faqs/faqs';
import { showLatestBuildVersion } from './docs/docs';

(function($) {
  "use strict";

  $(function() {

    initPlugins($);
    initForms($);
    initMailinglist($);
    initCheckout($);
    initAccount($);
    initFAQs();

    showLatestBuildVersion();

    initMobile($);

    initDriftTracking($);
    initDownloadTracking();

  });

})(jQuery);
