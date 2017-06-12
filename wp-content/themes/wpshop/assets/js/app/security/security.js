import find from 'ramda/src/find';
import propEq from 'ramda/src/propEq';
import unionWith from 'ramda/src/unionWith';
import eqProps from 'ramda/src/eqProps';
import { getUrlParams } from '../utils/utils';
import { saveAuthData, getStoredAuthData } from '../ws/ws';

/*

Checks if HMAC is valid

*/
function isValidHMAC($) {
  console.log(2);
  return new Promise(function (resolve, reject) {

  console.log(3, location.search);
  console.log('get url params', getUrlParams);

    var result = getUrlParams(location.search);
    console.log(4, result);
    var origHMAC = result.hmac;

    var dataToVerify = {
      code: result.code,
      shop: result.shop,
      state: result.state,
      timestamp: result.timestamp
    };

    console.log("dataToVerify: ", dataToVerify);

    var message = $.param(dataToVerify);
    var secret = 'd73e5e7fa67a54ac25a9af8ff8df3814';
    var finalDigest = crypto.createHmac('sha256', secret).update(message).digest('hex');

    console.log("Final val: ", finalDigest);
    console.log("Original hmac: ", origHMAC);

    if(finalDigest === origHMAC) {
      resolve("Valid HMAC");

    } else {
      reject("Invalid HMAC");
    }

  });

};


/*

Check if hostname is valid

*/
function isValidHostname($) {

  return new Promise(function (resolve, reject) {

    var result = getUrlParams(location.search);

    console.log("result.shop: ", result.shop);

    if(validator.isURL(result.shop)) {
      resolve();

    } else {
      reject("Invalid Hostname");

    }

  });

};


/*

Check if current nonce within the URL is valid. Checks
against the stored nonce values in the database.

*/
function isValidNonce($) {

  var url = getUrlParams(location.search),
      nonce = url.state;

  console.log("TESTING url: ", url);

  return new Promise(function (resolve, reject) {

    getStoredAuthData().then(function(response) {

      response = JSON.parse(response);

      console.log("TESTING response: ", response);

      var nonceMatches = find(propEq('nonce', nonce))(response);

      if(nonceMatches) {
        resolve(response);

      } else {
        reject("Nonce not found, error111!");
      }

    });

  });

};


/*

Update the stored consumer entry with 'code'

*/
function updateAuthDataWithCode($) {

  var ok = getStoredAuthData();

  console.log("okok: ", ok);

  ok.then(function(authData) {

    console.log("authDataauthData: ", authData);

    var url = getUrlParams(location.search);
    var nonce = url.state;

    var data = JSON.parse(authData);

    console.log("url: ", url);
    console.log("data: ", data);

    // Finds the client which matches the nonce in the URL
    var nonceMatch = find(propEq('nonce', nonce))(data);

    console.log("nonceMatch: ", nonceMatch);


    if(nonceMatch.nonce === url.state) {
      // Verified

      nonceMatch.code = url.code;

      var newnew = nonceMatch.url + "&shop=" + encodeURIComponent(url.shop) + "&auth=true";

      // window.location.href = newnew;

      nonceMatch.code = url.code;
      var finalRedirectURL = nonceMatch.url + "&shop=" + encodeURIComponent(url.shop) + "&auth=true";

      nonceMatch = [nonceMatch];

      // Merging updated client with everything else
      var newFinalList = unionWith(eqProps('domain'), nonceMatch, data);

      console.log("newFinalList: ", newFinalList);
      console.log("nonceMatch", nonceMatch);
      console.log("nonce", nonce);

      // Saving client records to database
      saveAuthData(JSON.stringify(newFinalList)).then(function(resp) {

        // At this point we've updated the authenticated consumer with the code
        // value sent from Shopify. We can now query for this value from the
        // consumer side.
        console.log('Newly saved: ', resp);
        console.log("finalRedirectURL", finalRedirectURL);
        window.location = finalRedirectURL;

      });

    }

  });

};


/*

Control center

*/
async function onShopifyAuth($) {
  console.log(1);
  await isValidHMAC($);
  console.log('Finished validating HMAC');
  await isValidHostname($);
  console.log('Finished validating Hostname');
  var nonce = await isValidNonce($);
  console.log('Finished validating nonce', nonce);
  await updateAuthDataWithCode($);
  console.log('Done');

}

// console.log('Auth page');

export { onShopifyAuth }
