import find from 'ramda/src/find';
import propEq from 'ramda/src/propEq';
import unionWith from 'ramda/src/unionWith';
import eqProps from 'ramda/src/eqProps';
import { getUrlParams, insertMessage, hideLoader } from '../utils/utils';
import { saveAuthData, getStoredAuthData } from '../ws/ws';

/*

Checks if HMAC is valid

*/
function isValidHMAC($) {

  return new Promise(function (resolve, reject) {

    var result = getUrlParams(location.search);
    var origHMAC = result.hmac;

    var dataToVerify = {
      code: result.code,
      shop: result.shop,
      state: result.state,
      timestamp: result.timestamp
    };

    var message = $.param(dataToVerify);
    var secret = 'd73e5e7fa67a54ac25a9af8ff8df3814';
    var finalDigest = crypto.createHmac('sha256', secret).update(message).digest('hex');

    if(finalDigest === origHMAC) {
      resolve("Valid HMAC");

    } else {
      // reject('Error: Invalid HMAC. Please try reconnecting your WordPress site to Shopify. If you\'re still experiencing the issue send an email to <a href="mailto:hello@wpshop.io">hello@wpshop.io</a> for immediate support.');
      resolve();
    }

  });

};


/*

Check if hostname is valid

*/
function isValidHostname($) {

  return new Promise(function (resolve, reject) {

    var result = getUrlParams(location.search);
    var shopifyDomainSuffix = '.myshopify.com';

    if (validator.isURL(result.shop) && result.shop.endsWith(shopifyDomainSuffix)) {
      resolve("Valid hostname");

    } else {
      reject('Error: Invalid Hostname. Please try reconnecting your WordPress site to Shopify. If you\'re still experiencing the issue send an email to <a href="mailto:hello@wpshop.io">hello@wpshop.io</a> for immediate support.');
    }

  });

};


/*

Check if current nonce within the URL is valid. Checks
against the stored nonce values in the database.

*/
function isValidNonce($) {

  return new Promise(function (resolve, reject) {

    var url = getUrlParams(location.search);

    if (!url.hasOwnProperty('state')) {
      reject('Error: Nonce not available. Please try reconnecting your WordPress site to Shopify. If you\'re still experiencing the issue send an email to <a href="mailto:hello@wpshop.io">hello@wpshop.io</a> for immediate support.');

    } else {

      var nonce = url.state;

      getStoredAuthData().then(function(response) {

        response = JSON.parse(response);

        var nonceMatches = find(propEq('nonce', nonce))(response);

        if(nonceMatches) {
          resolve(response);

        } else {
          reject('Error: Nonce invalid or not found. Please try reconnecting your WordPress site to Shopify. If you\'re still experiencing the issue send an email to <a href="mailto:hello@wpshop.io">hello@wpshop.io</a> for immediate support.');
        }

      });

    }

  });

};


/*

Update the stored consumer entry with 'code'

*/
function updateAuthDataWithCode($, authData) {

  return new Promise(function (resolve, reject) {

    var url = getUrlParams(location.search);

    if (!url.hasOwnProperty('state')) {
      reject('Error: Nonce not available. Please try reconnecting your WordPress site to Shopify. If you\'re still experiencing the issue send an email to <a href="mailto:hello@wpshop.io">hello@wpshop.io</a> for immediate support.');

    } else {

      var nonce = url.state;

      // Turn the JSON into JS object
      // var authData = JSON.parse(authData);

      // Finds the client which matches the nonce in the URL
      var nonceMatch = find(propEq('nonce', nonce))(authData);

      if (nonceMatch.nonce === url.state) {

        // Verified
        nonceMatch.code = url.code;

        var finalRedirectURL = nonceMatch.url + "&shop=" + encodeURIComponent(url.shop) + "&auth=true";

        // Conver to array so we can operate
        nonceMatch = [nonceMatch];

        // Merging updated client with everything else
        var updatedAuthenticatedSites = unionWith(eqProps('domain'), nonceMatch, authData);

        // Saving client records to database
        resolve({
          finalRedirectURL: finalRedirectURL,
          updatedAuthenticatedSites: updatedAuthenticatedSites
        });

      } else {
        reject('Error: Nonce does not match. Please try reconnecting your WordPress site to Shopify. If you\'re still experiencing the issue send an email to <a href="mailto:hello@wpshop.io">hello@wpshop.io</a> for immediate support.');

      }

    }

  });

};


/*

Control center

*/
async function onShopifyAuth($) {

  /*

  Step 1. Check if HMAC is valid

  */
  try {
    await isValidHMAC($);

  } catch(error) {

    insertMessage(error, 'error', true);
    hideLoader($('body'));

    return error;

  }


  /*

  Step 2. Check if hostname is valid

  */
  try {
    await isValidHostname($);

  } catch(error) {

    insertMessage(error, 'error', true);
    hideLoader($('body'));

    return error;

  }


  /*

  Step 3. Check if Nonce is valid

  */
  try {
    var authData = await isValidNonce($);

  } catch(error) {

    insertMessage(error, 'error', true);
    hideLoader($('body'));

    return error;

  }


  /*

  Step 4. Updating list of authenticated sites

  */
  try {
    var authDataResponse = await updateAuthDataWithCode($, authData);

  } catch(error) {

    insertMessage(error, 'error', true);
    hideLoader($('body'));

    return error;

  }


  /*

  Step 5. Saving newly authenticated site

  */
  try {
    await saveAuthData(JSON.stringify(authDataResponse.updatedAuthenticatedSites));

  } catch(error) {

    insertMessage(error, 'error', true);
    hideLoader($('body'));
    
    return error;

  }


  /*

  At this point we've updated the authenticated consumer with the code
  value sent from Shopify. We can now query for this value from the
  consumer side.

  */
  window.location = authDataResponse.finalRedirectURL;

}

export {
  onShopifyAuth
}
