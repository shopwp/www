/*

Control center

*/
function initDriftTracking($) {

  drift.on('ready',function(api, payload) {

    drift.on('startConversation', function (event) {

      dataLayer.push({
        'event': 'driftConversationStarted',
        'conversationId': event.conversationId
      });

    });

    drift.on('sidebarOpen',function(e) {

      dataLayer.push({
        'event': 'driftOpen'
      });

    });

  });

}


function initMailinglistTracking() {

  dataLayer.push({
    'event': 'mailinglistSubmission'
  });

}


function initDownloadTracking() {

  jQuery('.btn-download-free').on('click', function() {

    dataLayer.push({
      'event': 'downloadFree'
    });

  });

}

export { initDriftTracking, initMailinglistTracking, initDownloadTracking }
