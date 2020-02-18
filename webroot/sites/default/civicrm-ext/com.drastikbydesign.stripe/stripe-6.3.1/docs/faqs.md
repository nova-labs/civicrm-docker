## Terminology

#### CiviCRM <=> Stripe

* A CiviCRM **Contribution** is the equivalent of a Stripe **Invoice**.
* A CiviCRM **Payment** is the equivalent of a Stripe **Charge**.
* A CiviCRM **Contact** is the equivalent of a Stripe **Customer**.

#### Stripe

* Charge: A charge / payment that shows on a users credit card.

## Invoices and Charges?

For a **one-off contribution** an invoice is *NOT* created, so we have to use the Stripe `Charge ID`. In this case we set the contribution `trxn_id` = Stripe `Charge ID`.

For a **recurring contribution** an invoice is created for each contribution:

* We set the contribution `trxn_id` = Stripe `Invoice ID`.
* We set individual payments on that contribution (which could be a payment, a failed payment, a refund) to have `trxn_id` = Stripe `Charge ID`

## Uncaptured Payments / Charges

Stripe uses **PaymentIntents** to pre-authorise and authenticate a card.

These paymentIntents work in the same way as a pre-authorisation on Credit Card (such as a damage deposit on car-hire).
For this reason they can be problematic when there are multiple failures as a customer card will remain *authorised* for 7 days
and shows as a charge on the customer card. They can be manually cancelled via the Stripe Dashboard.

To mitigate this the Stripe extension tracks and records all paymentIntents created through CiviCRM and manages them
using a scheduled job `Job.process_stripe`.

The defaults for this are to cancel uncaptured payments after 1 hour and clear out old records (from the CiviCRM database) after three months.

## Payment Metadata

When we create a contribution in CiviCRM (Stripe Invoice/Charge) we add some metadata to that payment.

* The statement descriptor contains a parsable `contactID-contributionID` and then part of the description.
* The description contains the description, a parsable `contactID-contributionID` and then the CiviCRM (unique) invoice ID.

![Stripe Payment](/images/stripedashboard_paymentdetail.png)

## Customer Metadata

A new Stripe [**Customer**](https://stripe.com/docs/api/customers) is created the first time a contribution is created by them in CiviCRM.

Each time a new contribution is created the Stripe Customer metadata is updated.

The following metadata is created for a Stripe Customer:

* Contact Name, Email address
* Description (`CiviCRM: ` + site name).
* Contact ID.
* Link to CiviCRM contact record.
* CiviCRM version info (eg. `5.18.3 6.2`).

![Stripe Customer](/images/stripedashboard_customerdetail.png)

In addition, if you have enabled receipts (see [Setup](/setup)) the email address will be sent to Stripe and used to send a receipt to the contact.

## Postcode and Billing Address

Stripe performs advanced validation and detection of postal/zip code and whether it is required or not. So we use the postal code value from the stripe "element" as the "master".

The Stripe "element" collects the billing postcode.  CiviCRM uses the following logic for the billing postcode:
* If contact has existing billing address copy existing postcode to stripe element and **disable** the standard postcode field on the form.
* If contact has no billing address (or blank postcode) **hide** the standard postcode field and copy it from the stripe "element" when it is filled in.

