<?php
/**
 * https://civicrm.org/licensing
 */

/**
 * Stripe Subscription API
 *
 */

/**
 * StripeSubscription.Get API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_stripe_subscription_get_spec(&$spec) {
  $spec['subscription_id']['title'] = ts("Stripe Subscription ID");
  $spec['subscription_id']['type'] = CRM_Utils_Type::T_STRING;
  $spec['customer_id']['title'] = ts("Stripe Customer ID");
  $spec['customer_id']['type'] = CRM_Utils_Type::T_STRING;
  $spec['contribution_recur_id']['title'] = ts("Contribution Recur ID");
  $spec['contribution_recur_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['processor_id']['title'] = ts("Payment Processor ID");
  $spec['processor_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['end_time_id']['title'] = ts("End Time");
  $spec['end_time_id']['type'] = CRM_Utils_Type::T_INT;
}

/**
 * @deprecated This StripeSubscription.get is deprecated as of 5.2 as we now using recurring contribution instead of civicrm_stripe_subscriptions
 *
 * StripeSubscription.Get API
 *  This api will get entries from the civicrm_stripe_subscriptions table
 *
 * @param array $params
 * @see civicrm_api3_create_success
 *
 * @return array
 */
function civicrm_api3_stripe_subscription_get($params) {
  foreach ($params as $key => $value) {
    $index = 1;
    switch ($key) {
      case 'subscription_id':
      case 'customer_id':
        $where[$index] = "{$key}=%{$index}";
        $whereParam[$index] = [$value, 'String'];
        $index++;
        break;

      case 'contribution_recur_id':
      case 'processor_id':
      case 'end_time':
        $where[$index] = "{$key}=%{$index}";
        $whereParam[$index] = [$value, 'Integer'];
        $index++;
        break;

    }
  }


  $query = "SELECT * FROM civicrm_stripe_subscriptions ";
  if (count($where)) {
    $whereClause = implode(' AND ', $where);
    $query .= "WHERE {$whereClause}";
  }
  $dao = CRM_Core_DAO::executeQuery($query, $whereParam);

  while ($dao->fetch()) {
    $result = [
      'subscription_id' => $dao->subscription_id,
      'customer_id' => $dao->customer_id,
      'contribution_recur_id' => $dao->contribution_recur_id,
      'processor_id' => $dao->processor_id,
      'end_time' => $dao->end_time,
    ];
    $results[] = $result;
  }
  return civicrm_api3_create_success($results);
}

function civicrm_api3_stripe_subscription_updatetransactionids() {
  if (!CRM_Core_DAO::checkTableExists('civicrm_stripe_subscriptions')) {
    throw new CiviCRM_API3_Exception('Table civicrm_stripe_subscriptions is not used in Stripe >=5.2 and does not exist on your install. This API will be removed in a future release.');
  }

  $sql = "SELECT subscription_id, contribution_recur_id FROM civicrm_stripe_subscriptions";
  $dao = CRM_Core_DAO::executeQuery($sql);
  $counts = [
    'success' => 0,
    'failed' => 0
  ];
  while ($dao->fetch()) {
    if (!empty($dao->subscription_id) && !empty($dao->contribution_recur_id)) {
      try {
        civicrm_api3('ContributionRecur', 'create', ['id' => $dao->contribution_recur_id, 'trxn_id' => $dao->subscription_id]);
        $counts['success']++;
      }
      catch (Exception $e) {
        Civi::log()->debug('Error updating trxn_id for recur: ' . $dao->contribution_recur_id . ' trxn_id: ' . $dao->subscription_id);
        $counts['failed']++;
      }
    }
  }
  return civicrm_api3_create_success($counts);
}

/**
 * API function (used in 5021 upgrader) to copy trxn_id to processor_id in civicrm_contribution_recur table
 * processor_id (named subscriptionId) is the only value available to cancelSubscription in 5.9 (and earlier).
 * It is not ideal as processor_id is not guaranteed to be unique in the CiviCRM database (trxn_id is unique).
 *
 * @return array
 */
function civicrm_api3_stripe_subscription_copytrxnidtoprocessorid() {
  $sql = "SELECT cr.trxn_id, cr.processor_id, cr.payment_processor_id, cpp.class_name FROM civicrm_contribution_recur cr
LEFT JOIN civicrm_payment_processor AS cpp ON cr.payment_processor_id = cpp.id
WHERE cpp.class_name = 'Payment_Stripe'";
  $dao = CRM_Core_DAO::executeQuery($sql);
  $counts = [
    'updated' => 0,
  ];
  while ($dao->fetch()) {
    if (!empty($dao->trxn_id) && empty($dao->processor_id)) {
      $updateSQL = "UPDATE civicrm_contribution_recur
SET processor_id=%1
WHERE trxn_id=%1;";
      $updateParams = [1 => [$dao->trxn_id, 'String']];
      CRM_Core_DAO::executeQuery($updateSQL, $updateParams);
      $counts['updated']++;
    }
  }
  return civicrm_api3_create_success($counts);
}

/**
 * API to import a stripe subscription, create a customer, recur, contribution and optionally link to membership
 * You run it once for each subscription and it creates/updates a recurring contribution in civicrm (and optionally links it to a membership).
 *
 * @param array $params
 *
 * @return array
 * @throws \API_Exception
 * @throws \CiviCRM_API3_Exception
 * @throws \Stripe\Error\Api
 */
function civicrm_api3_stripe_subscription_import($params) {
  civicrm_api3_verify_mandatory($params, NULL, ['subscription_id', 'contact_id', 'payment_processor_id']);

  $paymentProcessor = \Civi\Payment\System::singleton()->getById($params['payment_processor_id'])->getPaymentProcessor();

  $processor = new CRM_Core_Payment_Stripe('', $paymentProcessor);
  $processor->setAPIParams();

  // Now re-retrieve the data from Stripe to ensure it's legit.
  $stripeSubscription = \Stripe\Subscription::retrieve($params['subscription_id']);

  // Create the stripe customer in CiviCRM
  $customerParams = [
    'customer_id' => CRM_Stripe_Api::getObjectParam('customer_id', $stripeSubscription),
    'contact_id' => $params['contact_id'],
    'processor_id' => (int) $params['payment_processor_id'],
  ];

  $customer = civicrm_api3('StripeCustomer', 'get', $customerParams);
  if (empty($customer['count'])) {
    civicrm_api3('StripeCustomer', 'create', $customerParams);
  }

  // Create the recur record in CiviCRM
  $contributionRecurParams = [
    'contact_id' => $params['contact_id'],
    'amount' => CRM_Stripe_Api::getObjectParam('plan_amount', $stripeSubscription),
    'currency' => CRM_Stripe_Api::getObjectParam('currency', $stripeSubscription),
    'frequency_unit' => CRM_Stripe_Api::getObjectParam('frequency_unit', $stripeSubscription),
    'frequency_interval' => CRM_Stripe_Api::getObjectParam('frequency_interval', $stripeSubscription),
    'start_date' => CRM_Stripe_Api::getObjectParam('plan_start', $stripeSubscription),
    'processor_id' => $params['subscription_id'],
    'trxn_id' => $params['subscription_id'],
    'contribution_status_id' => CRM_Stripe_Api::getObjectParam('status_id', $stripeSubscription),
    'cycle_day' => CRM_Stripe_Api::getObjectParam('cycle_day', $stripeSubscription),
    'auto_renew' => 1,
    'payment_processor_id' => $params['payment_processor_id'],
    'payment_instrument_id' => !empty($params['payment_instrument_id']) ? $params['payment_instrument_id'] : 'Credit Card',
    'financial_type_id' => !empty($params['financial_type_id']) ? $params['financial_type_id'] : 'Donation',
    'is_email_receipt' => !empty($params['is_email_receipt']) ? 1 : 0,
    'is_test' => isset($paymentProcessor['is_test']) && $paymentProcessor['is_test'] ? 1 : 0,
  ];
  if ($params['recur_id']) {
    $contributionRecurParams['id'] = $params['recur_id'];
  }

  $contributionRecur = civicrm_api3('ContributionRecur', 'create', $contributionRecurParams);

  // Get the invoices for the subscription
  $invoiceParams = [
    'customer' => CRM_Stripe_Api::getObjectParam('customer_id', $stripeSubscription),
    'limit' => 10,
  ];
  $stripeInvoices = \Stripe\Invoice::all($invoiceParams);
  foreach ($stripeInvoices->data as $stripeInvoice) {
    if (CRM_Stripe_Api::getObjectParam('subscription_id', $stripeInvoice) === $params['subscription_id']) {
      if (!empty(CRM_Stripe_Api::getObjectParam('description', $stripeInvoice))) {
        $sourceText = CRM_Stripe_Api::getObjectParam('description', $stripeInvoice);
      }
      elseif (!empty($params['contribution_source'])) {
        $sourceText = $params['contribution_source'];
      }
      else {
        $sourceText = 'Stripe: Manual import via API';
      }

      $contributionParams = [
        'contact_id' => $params['contact_id'],
        'total_amount' => CRM_Stripe_Api::getObjectParam('amount', $stripeInvoice),
        'currency' => CRM_Stripe_Api::getObjectParam('currency', $stripeInvoice),
        'receive_date' => CRM_Stripe_Api::getObjectParam('receive_date', $stripeInvoice),
        'trxn_id' => CRM_Stripe_Api::getObjectParam('charge_id', $stripeInvoice),
        'contribution_status_id' => CRM_Stripe_Api::getObjectParam('status_id', $stripeInvoice),
        'payment_instrument_id' => !empty($params['payment_instrument_id']) ? $params['payment_instrument_id'] : 'Credit Card',
        'financial_type_id' => !empty($params['financial_type_id']) ? $params['financial_type_id'] : 'Donation',
        'is_test' => isset($paymentProcessor['is_test']) && $paymentProcessor['is_test'] ? 1 : 0,
        'contribution_source' => $sourceText,
        'contribution_recur_id' => $contributionRecur['id'],
      ];

      $existingContribution = civicrm_api3('Contribution', 'get',
        [
          'contribution_test' => '',
          'trxn_id' => $contributionParams['trxn_id']
        ]);
      if (!empty($existingContribution['id'])) {
        $contributionParams['id'] = $existingContribution['id'];
      }
      elseif ($params['contribution_id']) {
        $contributionParams['id'] = $params['contribution_id'];
      }

      $contribution = civicrm_api3('Contribution', 'create', $contributionParams);
      break;
    }

  }

  // Link to membership record
  // By default we'll match the latest active membership, unless membership_id is passed in.
  if (!empty($params['membership_id'])) {
    $membershipParams = [
      'id' => $params['membership_id'],
      'contribution_recur_id' => $contributionRecur['id'],
    ];
    $membership = civicrm_api3('Membership', 'create', $membershipParams);
  }
  elseif (!empty($params['membership_auto'])) {
    $membershipParams = [
      'contact_id' => $params['contact_id'],
      'options' => ['limit' => 1, 'sort' => "id DESC"],
      'contribution_recur_id' => ['IS NULL' => 1],
      'is_test' => !empty($paymentProcessor['is_test']) ? 1 : 0,
      'active_only' => 1,
    ];
    $membership = civicrm_api3('Membership', 'get', $membershipParams);
    if (!empty($membership['id'])) {
      $membershipParams = [
        'id' => $membership['id'],
        'contribution_recur_id' => $contributionRecur['id'],
      ];
      $membership = civicrm_api3('Membership', 'create', $membershipParams);
    }
  }

  $results = [
    'subscription_id' => $params['subscription_id'],
    'customer_id' => CRM_Stripe_Api::getObjectParam('customer_id', $stripeSubscription),
    'recur_id' => $contributionRecur['id'],
    'contribution_id' => !empty($contribution['id'])? $contribution['id'] : NULL,
    'membership_id' => !empty($membership['id']) ? $membership['id'] : NULL,
  ];

  return civicrm_api3_create_success($results, $params, 'StripeSubscription', 'import');
}

function _civicrm_api3_stripe_subscription_import_spec(&$spec) {
  $spec['subscription_id']['title'] = ts("Stripe Subscription ID");
  $spec['subscription_id']['type'] = CRM_Utils_Type::T_STRING;
  $spec['subscription_id']['api.required'] = TRUE;
  $spec['contact_id']['title'] = ts("Contact ID");
  $spec['contact_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['contact_id']['api.required'] = TRUE;
  $spec['payment_processor_id']['title'] = ts("Payment Processor ID");
  $spec['payment_processor_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['payment_processor_id']['api.required'] = TRUE;

  $spec['recur_id']['title'] = ts("Contribution Recur ID");
  $spec['recur_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['contribution_id']['title'] = ts("Contribution ID");
  $spec['contribution_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['membership_id']['title'] = ts("Membership ID");
  $spec['membership_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['membership_auto']['title'] = ts("Link to existing membership automatically");
  $spec['membership_auto']['type'] = CRM_Utils_Type::T_BOOLEAN;
  $spec['membership_auto']['api.default'] = TRUE;
  $spec['financial_type_id'] = [
    'title' => 'Financial Type ID',
    'name' => 'financial_type_id',
    'type' => CRM_Utils_Type::T_INT,
    'pseudoconstant' => [
      'table' => 'civicrm_financial_type',
      'keyColumn' => 'id',
      'labelColumn' => 'name',
    ],
  ];
  $spec['payment_instrument_id']['api.aliases'] = ['payment_instrument'];
  $spec['contribution_source'] = [
    'title' => 'Contribution Source (optional description for contribution)',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

