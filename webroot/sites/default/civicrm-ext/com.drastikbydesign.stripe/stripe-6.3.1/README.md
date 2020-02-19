# CiviCRM Stripe Payment Processor

Integrates the Stripe payment processor (for Credit/Debit cards) into CiviCRM so you can use it to accept Credit / Debit card payments on your site.

* https://stripe.com/

Latest releases can be found here: https://civicrm.org/extensions/stripe-payment-processor

**Always read the [Release Notes](https://docs.civicrm.org/stripe/en/latest/release/release_notes/) carefully before upgrading!**

## Documentation
Please see: https://docs.civicrm.org/stripe/en/latest

## Configuration
All configuration is in the standard Payment Processors settings area in CiviCRM admin.
You will enter your "Publishable" & "Secret" key given by stripe.com.

## Installation
**The [mjwshared](https://lab.civicrm.org/extensions/mjwshared) extension is required and MUST be installed.**

**If using drupal webform or other integrations that use Contribution.transact API you should install the [contributiontransactlegacy](https://github.com/mjwconsult/civicrm-contributiontransactlegacy) extension to work around issues with that API.**

The extension will show up in the extensions browser for automated installation.
Otherwise, download and install as you would for any other CiviCRM extension.
