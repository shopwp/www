webpackHotUpdate(0,{199:function(e,r,i){"use strict";(function(e,d){function a(e){return e&&e.__esModule?e:{default:e}}function t(){var r=e("#edd_checkout_login_register"),i=e("#edd_checkout_user_info"),d=e("#edd_purchase_submit");e("#edd-user-login-submit .button").val("Login and checkout"),e("#card_number").attr("type","text"),i.addClass("animated zoomIn"),e(".wps-checkout-login-container").height(i.height()+60),e(".component-ask-existing").on("click",".button",function(a){var t,o;return b.default.async(function(n){for(;;)switch(n.prev=n.next){case 0:a.preventDefault(),t=e(this),o=t.parent(),r.removeClass("animated bounceInTop"),i.removeClass("animated bounceInTop"),d.removeClass("is-visible"),o.children().removeClass("is-active is-disabled"),t.addClass("is-active"),o.find(".button:not(.is-active)").addClass("is-disabled"),"login"===t.data("checkout-path")?(r.addClass("animated bounceInTop"),d.removeClass("is-visible")):(i.addClass("animated bounceInTop"),d.addClass("is-visible"));case 10:case"end":return n.stop()}},null,this)})}function o(){var r=e("#edd_cc_fields"),i=e("#edd_cc_address").prop("outerHTML"),d=e("#edd_purchase_submit").prop("outerHTML");r.find("#card_number").val(""),r=r.prop("outerHTML"),localStorage.setItem("wps-checkout-form",r+i+d)}function n(){e(".wps-checkout-login-container").after(e(localStorage.getItem("wps-checkout-form")))}function s(){e("#edd_cc_fields").remove(),e("#edd_cc_address").remove(),e("#edd_purchase_submit").remove()}function c(){var r=e("#edd_checkout_login_register"),i=e("#edd_checkout_user_info");e("#edd_purchase_submit"),e(".wps-welcome-link").on("click",function(d){d.preventDefault();var a=e(this).closest(".animated");"edd_checkout_login_register"===a.attr("id")?(n(),l(),a.animateCss("zoomOut",function(){a.removeClass("animated zoomIn zoomOut")}),i.animateCss("zoomIn",function(){console.log("done reg")})):(a.animateCss("zoomOut",function(){console.log("done active"),a.removeClass("animated zoomIn zoomOut")}),s(),r.animateCss("zoomIn",function(){console.log("done login")}))})}function l(){var r=new ScrollMagic.Controller,i=new ScrollMagic.Scene({duration:0,triggerElement:"#edd_cc_fields",triggerHook:0}).setClassToggle("#edd_checkout_cart_form","is-stuck").on("start",function(){console.log("111"),e("#edd_checkout_cart_form").toggleClass("animated fadeIn")}).setPin("#edd_checkout_cart_form");r.addScene([i]),u(r,i)}function u(r,i){e(".wps-welcome-link").on("click",function(d){d.preventDefault();var a=e(this).closest(".animated");"edd_checkout_login_register"===a.attr("id")||(r.destroy(),i.destroy())})}function m(){e(".is-registered-and-purchasing #edd_checkout_user_info input").prop("readonly",!0),e("#card_exp_month, #card_exp_year").attr("name","cardExpYear"),e("#card_number").attr("name","edd_credit_card"),e("#card_cvc").attr("name","edd_cvc"),e("#card_name").attr("name","edd_card_name"),e("#edd-purchase-button").prop("disabled",!0)}function _(){var r=e("#edd_purchase_form").validate({rules:{cardExpYear:{CCExp:{month:"#card_exp_month"}},edd_card_name:{required:!0},card_address:{required:!0},card_city:{required:!0},card_zip:{required:!0},billing_country:{required:!0},edd_credit_card:{creditcard:!0,required:!0},edd_email:{remote:{url:"/wp/wp-admin/admin-ajax.php",type:"post",data:{action:"wps_check_existing_username",email:function(){return e("#edd-email").val()}}},email:!0,required:!0},edd_cvc:{required:!0,pattern:/^[0-9]{3,4}$/},edd_first:{required:!0}},messages:{cardExpYear:"Please choose a valid date",CCExp:{month:"Valid month required"},edd_email:{email:"Please enter a valid email address",required:"Email is required",remote:'Email address already taken. Do you want to <a href="" class="wps-welcome-link">login</a> instead?'},edd_credit_card:{creditcard:"Please enter a valid credit card",required:"Credit card is required"},edd_first:{required:"First name is required"},edd_cvc:{required:"CVC is required",pattern:"Must be a number between 3-4 digits long"},edd_card_name:{required:"Name is required"},card_address:{required:"Billing Address is required"},card_city:{required:"Billing City is required"},card_zip:{required:"Billing Zip is required"},billing_country:{required:"Country is required"},card_state:{required:"State / province is required"}},highlight:function(r){e(r).parent().find(".is-valid").remove()},unhighlight:function(r){"edd_email"===e(r).attr("name")?setTimeout(function(){e(r).parent().find("label.error").is(":visible")||e(r).hasClass("error")||(e(r).parent().find(".is-valid").remove(),e(r).parent().find("label.error").hide(),e(r).parent().append('<span class="is-valid"></span>'))},250):(e(r).parent().find("label.error").is(":visible"),e(r).parent().find("label.error").is(":visible")||e(r).hasClass("error")||(e(r).parent().find(".is-valid").remove(),e(r).parent().find("label.error").hide(),e(r).parent().append('<span class="is-valid"></span>')))}});f(r)}function p(){var r=e("#edd_purchase_form input.required, #edd_purchase_form select.required"),i=r.filter(function(){return!this.value});i.length?console.log("Has empty required fields",i):(console.log("All required filled",i),console.log("eeeeerrrrrrs: ",d.errorList),d.errorList.length?e("#edd-purchase-button").prop("disabled",!0):e("#edd-purchase-button").prop("disabled",!1))}function f(r){var i=e("#edd_purchase_form input, #edd_purchase_form select");i.on("keyup change blur",(0,q.default)(p,150))}function h(){var r=e("#edd-stripe-payment-errors"),i=e("#edd-purchase-button");i.on("click",function(){r.empty();var i=e(this),d=setInterval(function(){var e=i.closest("form").find("#edd-stripe-payment-errors .edd_errors"),r=i.closest("form").find("#edd-email-error").is(":visible");e.length||r?(console.log("here"),(0,C.enableForm)(i.closest("form")),(0,C.hideLoader)(i.closest("form")),clearInterval(d)):(console.log("sfssdfhere"),(0,C.disableForm)(i.closest("form")),(0,C.showLoader)(i.closest("form")))},200)})}function v(){t(),c(),(0,y.initCheckoutSteps)(),l(),m(),_(),h(),o(),console.log("22222")}Object.defineProperty(r,"__esModule",{value:!0}),r.initCheckout=void 0;var g=i(34),b=a(g),k=i(331),q=a(k),C=i(45),y=i(200);console.log("ok ok"),r.initCheckout=v}).call(r,i(8),i(60))}});