import {
  getDoc
} from "../ws/ws";

import {
  selectText
} from "../utils/utils";

/*

On click

*/
function onDocClick($) {

  $('.doc-collapsable-trigger').on('click', function() {
    $(this).next().slideToggle();
    $(this).find('svg').toggleClass('fa-minus-circle fa-plus-circle');
  });


  $('.doc-term').on('click', async function(e) {

    e.stopPropagation();
    e.preventDefault();

    var $doc = jQuery(this);

    if (!$doc.hasClass('is-current-doc')) {

      $('.is-docs > .fa-cog').addClass('is-visible fa-spin');
      $('.docs-content-loader').addClass('is-visible');

      $doc.addClass('is-loading');
      jQuery('.doc-content-wrapper').addClass('is-loading');
      jQuery('.doc-term.is-current-doc').removeClass('is-current-doc');
      $doc.addClass('is-current-doc');

      console.log('$doc: ', $doc);

      setTimeout(function() {
        $doc.find('.doc-type .svg-inline--fa').addClass('fa-spin');
      }, 1);


      var data = await getDoc( $doc.data('doc-id') );

      data = JSON.parse(data);

      showDocContent(data.content);
      jQuery('.doc-content-wrapper').removeClass('is-loading');
      $doc.removeClass('is-loading');
      $('.is-docs > .fa-cog').removeClass('is-visible fa-spin');

      window.history.pushState("object or string", "Title", data.url);





      // jQuery('html, body').animate({
      //   scrollTop: jQuery('.main').offset().top - 150
      // }, 200);

      // DISQUS.reset({
      //   reload: true,
      //   config: function () {
      //     this.page.identifier = $doc.data('doc-id').toString();
      //     this.page.url = "https://staging.wpshop.dev/#!newthread";
      //   }
      // });

    }

  });

}


/*

Show Doc Content

*/
function showDocContent(docContent) {

  jQuery('.main').empty().append( jQuery('<div class="doc-content-wrapper">' + docContent + '</div>') );

  hljs.initHighlighting.called = false;
  hljs.initHighlighting();

  jQuery('.docs-content-loader').removeClass('is-visible');
}


/*

Accordion

*/
function getLatestBuild() {

  var options = {
    method: 'GET',
    url: 'https://api.travis-ci.org/repo/16428850',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('Travis-API-Version', '3');
      xhr.setRequestHeader('Authorization', 'token TorU8IJ9DU4scRFdwowoiw');
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('Content-Type', 'application/json');
    },
    dataType: 'json'
  };

  return jQuery.ajax(options);

}



function getLatestVersion() {

  var options = {
    method: 'GET',
    url: 'https://api.github.com/repos/arobbins/wp-shopify/tags',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('Authorization', 'token 256460cc4351f765540bc4f496cb45ccdbabad82');
      xhr.setRequestHeader('Accept', 'application/vnd.github.v3+json');
      xhr.setRequestHeader('Content-Type', 'application/json');
    },
    dataType: 'json'
  };

  return jQuery.ajax(options);

}


/*

Accordion

*/
async function showLatestBuildVersion() {

  try {
    // var response = await getLatestBuild();
    var response = await getLatestVersion();

    jQuery('.docs-version').html('v' + response[0].name);

  } catch (e) {
    console.error('docs version error: ', e);

  }

}


/*

Init Docs

*/
function initDocs($) {

  onDocClick($);
  showLatestBuildVersion();

}

export { initDocs }
