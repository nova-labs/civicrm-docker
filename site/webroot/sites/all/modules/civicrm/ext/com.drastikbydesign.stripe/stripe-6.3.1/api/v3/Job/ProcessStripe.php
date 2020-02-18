<?php
/**
 * This job performs various housekeeping actions related to the Stripe payment processor
 *
 * @param array $params
 *
 * @return array
 *   API result array.
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_job_process_stripe($params) {
  $results = [];

  if ($params['delete_old'] !== 0 && !empty($params['delete_old'])) {
    // Delete all locally recorded paymentIntents that are older than 3 months
    $oldPaymentIntents = civicrm_api3('StripePaymentintent', 'get', [
      'status' => ['IN' => ["succeeded", "cancelled"]],
      'created_date' => ['<' => $params['delete_old']],
    ]);
    foreach ($oldPaymentIntents['values'] as $id => $detail) {
      civicrm_api3('StripePaymentintent', 'delete', ['id' => $id]);
      $results['deleted'][$id] = $detail['paymentintent_id'];
    }
  }

  if ($params['cancel_incomplete'] !== 0 && !empty($params['cancel_incomplete'])) {
    // Cancel incomplete paymentIntents after 1 hour
    $incompletePaymentIntents = civicrm_api3('StripePaymentintent', 'get', [
      'status' => ['NOT IN' => ["succeeded", "cancelled"]],
      'created_date' => ['<' => $params['cancel_incomplete']],
    ]);
    foreach ($incompletePaymentIntents['values'] as $id => $detail) {
      try {
        /** @var \CRM_Core_Payment_Stripe $paymentProcessor */
        $paymentProcessor = Civi\Payment\System::singleton()
          ->getById($detail['payment_processor_id']);
        $paymentProcessor->setAPIParams();
        $intent = \Stripe\PaymentIntent::retrieve($detail['paymentintent_id']);
        $intent->cancel(['cancellation_reason' => 'abandoned']);
      } catch (Exception $e) {
      }
      civicrm_api3('StripePaymentintent', 'create', [
        'id' => $id,
        'status' => 'cancelled'
      ]);
      $results['cancelled'][$id] = $detail['paymentintent_id'];
    }
  }

  return civicrm_api3_create_success($results, $params);
}

/**
 * Action Payment.
 *
 * @param array $params
 *
 * @return array
 */
function _civicrm_api3_job_process_stripe_spec(&$params) {
  $params['delete_old']['api.default'] = '-3 month';
  $params['delete_old']['title'] = 'Delete old records after (default: -3 month)';
  $params['delete_old']['description'] = 'Delete old records from database. Specify 0 to disable. Default is "-3 month"';
  $params['cancel_incomplete']['api.default'] = '-1 hour';
  $params['cancel_incomplete']['title'] = 'Cancel incomplete records after (default: -1hour)';
  $params['cancel_incomplete']['description'] = 'Cancel incomplete paymentIntents in your stripe account. Specify 0 to disable. Default is "-1hour"';
}
