/*

Control center

*/
function initDriftTracking($) {

  drift.on('ready',function(api, payload) {
    console.log('Drift ready');

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


export { initDriftTracking, initMailinglistTracking }
