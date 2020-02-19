/**
 * This handles confirmation actions on the "Thankyou" pages for contribution/event workflows.
 */
CRM.$(function($) {
  debugging("civicrmStripeConfirm loaded");

  if (typeof CRM.vars.stripe === 'undefined') {
    debugging('CRM.vars.stripe not defined! Not a Stripe processor?');
    return;
  }
  switch (CRM.vars.stripe.paymentIntentStatus) {
    case 'succeeded':
    case 'cancelled':
      debugging('paymentIntent: ' . CRM.vars.stripe.paymentIntentStatus);
      return;
  }

  checkAndLoad();

  if (typeof stripe === 'undefined') {
    stripe = Stripe(CRM.vars.stripe.publishableKey);
  }

  handleCardConfirm();

  // On initial load...
  var stripe;
  var stripeLoading = false;

  // Disable the browser "Leave Page Alert" which is triggered because we mess with the form submit function.
  window.onbeforeunload = null;

  function handleServerResponse(result) {
    debugging('handleServerResponse');
    if (result.error) {
      // Show error from server on payment form
      // displayError(result);
    } else if (result.requires_action) {
      // Use Stripe.js to handle required card action
      handleAction(result);
    } else {
      // All good, nothing more to do
      debugging('success - payment captured');
    }
  }

  function handleAction(response) {
    switch (CRM.vars.stripe.paymentIntentMethod) {
      case 'automatic':
        stripe.handleCardPayment(response.payment_intent_client_secret)
          .then(function (result) {
            if (result.error) {
              // Show error in payment form
              handleCardConfirm();
            }
            else {
              // The card action has been handled
              debugging('card payment success');
              handleCardConfirm();
            }
          });
        break;

      case 'manual':
        stripe.handleCardAction(response.payment_intent_client_secret)
          .then(function (result) {
            if (result.error) {
              // Show error in payment form
              handleCardConfirm();
            }
            else {
              // The card action has been handled
              debugging('card action success');
              handleCardConfirm();
            }
          });
        break;
    }
  }

  function handleCardConfirm() {
    debugging('handle card confirm');
    // Send paymentMethod.id to server
    var url = CRM.url('civicrm/stripe/confirm-payment');
    $.post(url, {
      payment_intent_id: CRM.vars.stripe.paymentIntentID,
      capture: true,
      id: CRM.vars.stripe.id,
    }).then(function (result) {
      // Handle server response (see Step 3)
      handleServerResponse(result);
    });
  }

  function checkAndLoad() {
    if (typeof Stripe === 'undefined') {
      if (stripeLoading) {
        return;
      }
      stripeLoading = true;
      debugging('Stripe.js is not loaded!');

      $.getScript("https://js.stripe.com/v3", function () {
        debugging("Script loaded and executed.");
        stripeLoading = false;
      });
    }
  }

  function debugging(errorCode) {
    // Uncomment the following to debug unexpected returns.
    if ((typeof(CRM.vars.stripe) === 'undefined') || (Boolean(CRM.vars.stripe.jsDebug) === true)) {
      console.log(new Date().toISOString() + ' civicrm_stripe.js: ' + errorCode);
    }
  }

});
