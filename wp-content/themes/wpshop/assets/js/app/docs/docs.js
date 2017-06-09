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

    console.log('clicked', $(this).text());
    var $doc = $(this);
    console.log('$doc: ', $doc);

    Pace.restart();
    Pace.track(async function() {

      console.log('inside stop');

      var data = await getDoc( $doc.data('doc-id') );

      data = JSON.parse(data);

      console.log("data.content: ", $doc.data('doc-id').toString());
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
    console.log('Doneeeeeeeeeee highlighting');
  });

}


/*

Init Docs

*/
function initDocs($) {
  onDocClick($);
}

export { initDocs }

// console.log("Docs page");
// initDocs();
