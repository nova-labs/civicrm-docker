<?php
/**
 * https://civicrm.org/licensing
 */

class CRM_Stripe_Api {

  public static function getObjectParam($name, $stripeObject) {
    $className = get_class($stripeObject);
    switch ($className) {
      case 'Stripe\Charge':
        switch ($name) {
          case 'charge_id':
            return (string) $stripeObject->id;

          case 'failure_code':
            return (string) $stripeObject->failure_code;

          case 'failure_message':
            return (string) $stripeObject->failure_message;

          case 'refunded':
            return (bool) $stripeObject->refunded;

          case 'amount_refunded':
            return (float) $stripeObject->amount_refunded / 100;

          case 'customer_id':
            return (string) $stripeObject->customer;

          case 'balance_transaction':
            return (string) $stripeObject->balance_transaction;

          case 'receive_date':
            return self::formatDate($stripeObject->created);

          case 'invoice_id':
            return (string) $stripeObject->invoice;

          case 'captured':
            return (bool) $stripeObject->captured;

        }
        break;

      case 'Stripe\Invoice':
        switch ($name) {
          case 'charge_id':
            return (string) $stripeObject->charge;

          case 'invoice_id':
            return (string) $stripeObject->id;

          case 'receive_date':
            return self::formatDate($stripeObject->created);

          case 'subscription_id':
            return (string) $stripeObject->subscription;

          case 'amount':
            return (string) $stripeObject->amount_due / 100;

          case 'amount_paid':
            return (string) $stripeObject->amount_paid / 100;

          case 'amount_remaining':
            return (string) $stripeObject->amount_remaining / 100;

          case 'currency':
            return (string) mb_strtoupper($stripeObject->currency);

          case 'status_id':
            if ((bool) $stripeObject->paid) {
              return 'Completed';
            }
            else {
              return 'Pending';
            }

          case 'description':
            return (string) $stripeObject->description;

          case 'customer_id':
            return (string) $stripeObject->customer;

          case 'failure_message':
            $stripeCharge = \Stripe\Charge::retrieve($stripeObject->charge);
            return (string) $stripeCharge->failure_message;

        }
        break;

      case 'Stripe\Subscription':
        switch ($name) {
          case 'frequency_interval':
            return (string) $stripeObject->plan->interval_count;

          case 'frequency_unit':
            return (string) $stripeObject->plan->interval;

          case 'plan_amount':
            return (string) $stripeObject->plan->amount / 100;

          case 'currency':
            return (string) mb_strtoupper($stripeObject->plan->currency);

          case 'plan_id':
            return (string) $stripeObject->plan->id;

          case 'plan_name':
            return (string) $stripeObject->plan->name;

          case 'plan_start':
            return self::formatDate($stripeObject->start_date);

          case 'cancel_date':
            return self::formatDate($stripeObject->canceled_at);

          case 'cycle_day':
            return date("d", $stripeObject->billing_cycle_anchor);

          case 'subscription_id':
            return (string) $stripeObject->id;

          case 'status_id':
            switch ($stripeObject->status) {
              case \Stripe\Subscription::STATUS_ACTIVE:
                return CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'In Progress');

              case \Stripe\Subscription::STATUS_CANCELED:
                return CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Cancelled');

            }

          case 'customer_id':
            return (string) $stripeObject->customer;
        }
        break;
    }
    return NULL;
  }

  /**
   * Return a formatted date from a stripe timestamp or NULL if not set
   * @param int $stripeTimestamp
   *
   * @return string|null
   */
  private static function formatDate($stripeTimestamp) {
    return $stripeTimestamp ? date('YmdHis', $stripeTimestamp) : NULL;
  }

  public static function getParam($name, $stripeObject) {
    // Common parameters
    switch ($name) {
      case 'customer_id':
        return (string) $stripeObject->customer;

      case 'event_type':
        return (string) $stripeObject->type;

      case 'id':
        return (string) $stripeObject->id;

      case 'previous_plan_id':
        if (preg_match('/\.updated$/', $stripeObject->type)) {
          return (string) $stripeObject->data->previous_attributes->plan->id;
        }
        break;
    }
    return NULL;
  }

}
