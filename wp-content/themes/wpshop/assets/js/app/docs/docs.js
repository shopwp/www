import {
  getDoc
} from "../ws/ws";

/*

On click

*/
function onDocClick($) {

  // window.paceOptions = {
  //   ajax: {
  //    trackMethods: ["GET", "POST"]
  //  }
  // }
  Pace.restart();

  $('.doc-term').on('click', function(e) {

    var $doc = jQuery(this);

    if (!$doc.hasClass('is-current-doc')) {

      jQuery('.entry-content').addClass('is-loading');
      jQuery('.doc-term.is-current-doc').removeClass('is-current-doc');
      $doc.addClass('is-current-doc')

      Pace.restart();
      Pace.track(async function() {

        var data = await getDoc( $doc.data('doc-id') );

        data = JSON.parse(data);

        showDocContent(data.content);
        jQuery('.entry-content').removeClass('is-loading');

        var url = "/docs/" + data.slug;

        window.history.pushState("object or string", "Title", url);

        // jQuery('html, body').animate({
        //   scrollTop: jQuery('.main').offset().top - 150
        // }, 200);

        DISQUS.reset({
          reload: true,
          config: function () {
            this.page.identifier = $doc.data('doc-id').toString();
            this.page.url = "https://staging.wpshop.dev/#!newthread";
          }
        });


      });

    }

  });

}


/*

Show Doc Content

*/
function showDocContent(docContent) {

  jQuery('.main').empty().append( jQuery('<div class="entry-content">' + docContent + '</div>') );
  jQuery('.entry-content').after( jQuery('<div id="disqus_thread"></div>') );

  Prism.highlightAll();

}


/*

Init Docs

*/
function initDocs($) {

  onDocClick($);

}

export { initDocs }
