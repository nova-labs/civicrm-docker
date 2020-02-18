## Information

Releases use the following numbering system:
**{major}.{minor}.{incremental}**

Where:
* major: Major refactoring or rewrite - make sure you read and test very carefully!
* minor: Breaking change in some circumstances, or a new feature. Read carefully and make sure you understand the impact of the change.
* incremental: A "safe" change / improvement. Should *always* be safe to upgrade.

## Release 0.6

* Improve updateContributionRefund() function to handle new `order_reference` field and use `Payment.create` API.
* Simply calls in Contribution.getbalance to improve performance.
* Add check to warn if nfp worldpay extension is installed as it breaks things!
* Add currency symbol to Contribution.getbalance

## Release 0.5.1

* Fix getBillingEmail() to work in more circumstances and add tests

## Release 0.5

* Add Contribution.GetBalance API

## Release 0.4.6

* Fix missing return array on getTokenParameter.

## Release 0.4.5

* Remove setTokenParameter, modify getTokenParameter as we're now using pre_approval_parameters in Stripe 6.2

## Release 0.4.4

* Record a full refund correctly

## Release 0.4.3

* Improvements to get/setTokenParameter.
* Add js validation to event registration form.

## Release 0.4.2

* Fix params passed to repeatTransaction - this was causing some repeating contributions to fail.

## Release 0.4.1

* Fix 'is not boolean' error on IPNs. `getIsTestMode()` was returning TRUE/FALSE but the API requires 1/0.

## Release 0.4

* Fix issue with non-default currency on form when you can choose from more than one payment processor on the form.
* Add `getTokenParameter()`/`setTokenParameter()` functions to MJWTrait which should be used when setting parameters
via javascript (eg. Stripe `paymentIntentID`) which are required when the payment is actually processed (via `doPayment()`).

## Release 0.3

* Major refactor of MJWIPNTrait.
* Add function to update the transaction ID for a payment related to a contribution.

## Release 0.2

* Add function to get configured currency for contributionpage/event registration page.

## Release 0.1

* Initial release
