import {
  getDoc
} from "../ws/ws";

import {
  selectText,
  copyToClipboard
} from "../utils/utils";

import {
  getDocsToken
} from "../utils/utils-token";

import {
  initAccordions
} from "../forms/forms";



function toggleChangelogs() {

  jQuery('.changelog-type').on('click', function() {

    var type = jQuery(this).data('changelog-type');

    jQuery('.changelog-type').removeClass('is-active');
    jQuery(this).addClass('is-active');

    jQuery('.changelog-wrapper.is-active').removeClass('is-active');
    jQuery('.changelog-wrapper[data-changelog-type=' + type + ']').addClass('is-active');

    jQuery('.changelog-type-heading span').text(type);

  });

}



/*

On click

*/
function onDocClick($) {

  jQuery('.doc-collapsable-trigger').on('click', function() {
    jQuery(this).next().slideToggle();
    jQuery(this).find('svg').toggleClass('fa-minus-circle fa-plus-circle');
  });


  jQuery('.doc-term').on('click', async function(e) {

    e.stopPropagation();
    e.preventDefault();

    var $doc = jQuery(this);

    if (!$doc.hasClass('is-current-doc')) {

      jQuery('.is-docs > .fa-cog').addClass('is-visible fa-spin');
      jQuery('.docs-content-loader').addClass('is-visible');

      $doc.addClass('is-loading');
      jQuery('.doc-content-wrapper').addClass('is-loading');
      jQuery('.doc-term.is-current-doc').removeClass('is-current-doc');
      $doc.addClass('is-current-doc');


      setTimeout(function() {
        $doc.find('.doc-type .svg-inline--fa').addClass('fa-spin');
      }, 1);


      var data = await getDoc( $doc.data('doc-id') );
      data = JSON.parse(data);

      showDocContent(data.content);
      jQuery('.doc-content-wrapper').removeClass('is-loading');
      $doc.removeClass('is-loading');
      jQuery('.is-docs > .fa-cog').removeClass('is-visible fa-spin');

      window.history.pushState("object or string", "Title", data.url);


      copyToClipboard();
      initAccordions(jQuery);


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
  Rainbow.color();
  jQuery('.docs-content-loader').removeClass('is-visible');

}


/*

Get Latest Plugin Version

*/
async function getLatestVersion() {

  var token = await getDocsToken();

  var options = {
    method: 'GET',
    url: 'https://api.github.com/repos/arobbins/wp-shopify-pro/tags',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('Authorization', 'Token ' + token);
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

    var response = await getLatestVersion();

    jQuery('.docs-version').html('v' + response[0].name);

  } catch (e) {
    console.error('docs version error: ', e);

  }

}


/*

Init Docs

*/
function initDocs() {

  onDocClick();
  showLatestBuildVersion();
  copyToClipboard();
  initAccordions(jQuery);
  toggleChangelogs();

}

// Only show Pace on docs template
if (jQuery('body').hasClass('docs-template-default')) {
  initDocs();  
}


export { showLatestBuildVersion }
