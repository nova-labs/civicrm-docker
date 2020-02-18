# Webhooks

## Overview
If you are using recurring contributions with Stripe you **MUST** setup a webhook and check that it is working. Otherwise contributions will never be marked "Completed".

If you are not using recurring contributions the webhook, if available, will update contributions if they are refunded / cancelled / failed.

## Configuring Webhooks
From version 5.4.1 the extension manages / creates the webhooks. A system check is run to verify if the webhooks are created and have the correct parameters. If not a *Wizard* is provided to create them for you.

To check if webhooks are configured correctly login to your Stripe Dashboard and look at **Developers > Webhooks**
 
## Notifications

Stripe notifies CiviCRM in the following circumstances:

* A Charge is successful (not normally used as we are already notified during the actual payment process).
* A Charge fails - sometimes a charge may be delayed (eg. for Fraud checks) and later fails.
* A Charge is refunded - if a charge is refunded via the Stripe Dashboard it will update in CiviCRM.

* An invoice is created and paid.
* An invoice payment fails.
* A subscription is cancelled.
* A subscription is updated.
