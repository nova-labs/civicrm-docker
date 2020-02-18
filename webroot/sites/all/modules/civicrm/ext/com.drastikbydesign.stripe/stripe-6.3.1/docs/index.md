# Stripe Payment Processor for CiviCRM.
Integrates the Stripe payment processor (for Credit/Debit cards) into CiviCRM so you can use it to accept Credit / Debit card payments on your site.

[![Stripe Logo](/images/stripe.png)](https://stripe.com/)

View/Download this extension in the [Extension Directory](https://civicrm.org/extensions/stripe-payment-processor).

## Supports
* PSD2 / SCA payments on one-off payments, partial support for recurring payments (may not be able to authorise card in some cases).
* Cancellation of subscriptions from Stripe / CiviCRM.
* Refund of payments from Stripe.

### Does not support
* Updating Stripe subscriptions from CiviCRM.

## Compatibility / Requirements
* CiviCRM 5.19+
* PHP 7.1+
* Jquery 1.10 (Use jquery_update module on Drupal).
* Drupal 7 / Joomla / Wordpress (latest supported release). *Not currently tested with other CMS but it may work.*
* Stripe API version: 2019-12-03
* Drupal webform_civicrm 7.x-4.28+ (if using webform integration) - does NOT support test mode:
  * If using test mode with drupal webform_civicrm (4.x) you need [this patch for webform_civicrm](https://github.com/colemanw/webform_civicrm/pull/266).
  * If using drupal webform_civicrm (4.x) you may need [this patch for CiviCRM core](https://github.com/civicrm/civicrm-core/pull/15340).

* [MJWShared extension](https://civicrm.org/extensions/mjwshared) version 0.6.

**Please ensure that you are running the ProcessStripe scheduled job every hour or you will have issues with failed/uncaptured payments appearing on customer credit cards and blocking their balance for up to a week!**


## Troubleshooting
Under *Administer->CiviContribute->Stripe Settings* you can find a setting:
* Enable Stripe Javascript debugging?

> This can be switched on to output debug info to the browser console and can be used to debug problems with submitting your payments.

## Support and Maintenance
This extension is supported and maintained by [![MJW Consulting](/images/mjwconsulting.jpg)](https://www.mjwconsult.co.uk) with the help and support of the CiviCRM community.
