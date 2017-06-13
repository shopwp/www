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

    var $doc = $(this);


    Pace.restart();
    Pace.track(async function() {


      var data = await getDoc( $doc.data('doc-id') );

      data = JSON.parse(data);

      showDocContent(data.content);

      var url = "/docs/" + data.slug;

      window.history.pushState("object or string", "Title", url);


      DISQUS.reset({
        reload: true,
        config: function () {
          this.page.identifier = $doc.data('doc-id').toString();
          this.page.url = "http://wpshop.dev/#!newthread";
        }
      });


    });

  });

}


/*

Show Doc Content

*/
function showDocContent(docContent) {

  jQuery('.main').empty().append( jQuery('<div class="entry-content">' + docContent + '</div>') );
  jQuery('.entry-content').after( jQuery('<div id="disqus_thread"></div>') );

  Prism.highlightAll(true, function() {

  });

}


/*

Init Docs

*/
function initDocs($) {
  onDocClick($);
}

export { initDocs }
