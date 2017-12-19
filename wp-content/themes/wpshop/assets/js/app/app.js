import '../../css/app/app.scss';

import { onShopifyAuth } from './security/security';
import { initPlugins } from './plugins/plugins';
import { initForms } from './forms/forms';
import { initMailinglist } from './mailinglist/mailinglist';
import { initCheckout } from './checkout/checkout';
import { initAccount } from './account/account';
import { initDocs } from './docs/docs';
import { initMobile } from './mobile/mobile';
import { initDriftTracking } from './analytics/analytics';
import { initFAQs } from './faqs/faqs';

(function($) {
  "use strict";

  $(function() {

    // Only show Pace on docs template
    // if ($('body').hasClass('docs-template-default')) {
    //   Pace.restart();
    //
    // } else {
    //   Pace.stop();
    // }

    initPlugins($);
    initForms($);
    initMailinglist($);
    initCheckout($);
    initAccount($);
    initDocs($);
    initFAQs();

    initMobile($);

    initDriftTracking($);

    if (window.location.pathname === '/auth') {
      onShopifyAuth($);
    }


  });

})(jQuery);
