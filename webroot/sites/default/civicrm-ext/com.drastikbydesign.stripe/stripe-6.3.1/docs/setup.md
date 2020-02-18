# Setup and configuration
Please make sure you have read and followed the instructions under [Install](/install) first.

## Receipts

In addition to the receipts that CiviCRM can send, Stripe will send it's own receipt for payment by default.

If you wish to disable this under *Administer->CiviContribute->Stripe Settings* you can find a setting that allows you to disable Stripe from sending receipts:

* Allow Stripe to send a receipt for one-off payments?

## Cancelling abandoned payment attempts

A scheduled job (Job.process_stripe) automatically cancels abandoned (uncaptured) paymentIntents after 24 hours.
