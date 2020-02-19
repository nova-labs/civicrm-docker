<?php
/**
 * https://civicrm.org/licensing
 */

/**
 * Shared payment IPN functions that should one day be migrated to CiviCRM core
 *
 * Trait CRM_Core_Payment_MJWIPNTrait
 */
trait CRM_Core_Payment_MJWIPNTrait {

  /**
   * @var \CRM_Core_Payment Payment processor
   */
  protected $_paymentProcessor;

  /**
   * Do we send an email receipt for each contribution?
   *
   * @var int
   */
  protected $is_email_receipt = NULL;

  /**
   * The recurring contribution ID associated with the transaction
   * @var int
   */
  protected $contribution_recur_id = NULL;

  /**
   *  The IPN event type
   * @var string
   */
  protected $event_type = NULL;

  /**
   * Set the value of is_email_receipt to use when a new contribution is received for a recurring contribution
   * If not set, we respect the value set on the ContributionRecur entity.
   *
   * @param int $sendReceipt The value of is_email_receipt
   */
  public function setSendEmailReceipt($sendReceipt) {
    switch ($sendReceipt) {
      case 0:
        $this->is_email_receipt = 0;
        break;

      case 1:
        $this->is_email_receipt = 1;
        break;

      default:
        $this->is_email_receipt = 0;
    }
  }

  /**
   * Get the value of is_email_receipt to use when a new contribution is received for a recurring contribution
   * If not set, we respect the value set on the ContributionRecur entity.
   *
   * @return int
   * @throws \CiviCRM_API3_Exception
   */
  public function getSendEmailReceipt() {
    if (isset($this->is_email_receipt)) {
      return (int) $this->is_email_receipt;
    }
    if (!empty($this->contribution_recur_id)) {
      $this->is_email_receipt = civicrm_api3('ContributionRecur', 'getvalue', [
        'return' => "is_email_receipt",
        'id' => $this->contribution_recur_id,
      ]);
    }
    return (int) $this->is_email_receipt;
  }

  /**
   * Get the payment processor
   *   The $_GET['processor_id'] value is set by CRM_Core_Payment::handlePaymentMethod.
   */
  protected function getPaymentProcessor() {
    $paymentProcessorId = (int) CRM_Utils_Array::value('processor_id', $_GET);
    if (empty($paymentProcessorId)) {
      $this->exception('Failed to get payment processor id');
    }

    try {
      $this->_paymentProcessor = \Civi\Payment\System::singleton()->getById($paymentProcessorId);
    }
    catch(Exception $e) {
      $this->exception('Failed to get payment processor');
    }
  }

  /**
   * Record a refund on a contribution
   * This wraps around the payment.create API to support earlier releases than features were available
   *
   * Examples:
   * $result = civicrm_api3('Payment', 'create', [
   *   'contribution_id' => 590,
   *   'total_amount' => -3,
   *   'trxn_date' => 20191105200300,
   *   'trxn_result_code' => "Test a refund with fees",
   *   'fee_amount' => -0.25,
   *   'trxn_id' => "abctx123",
   *   'order_reference' => "abcor123",
   * ]);
   *
   *  Returns:
   * "is_error": 0,
   * "version": 3,
   * "count": 1,
   * "id": 465,
   * "values": {
   *   "465": {
   *     "id": "465",
   *     "from_financial_account_id": "7",
   *     "to_financial_account_id": "6",
   *     "trxn_date": "20191105200300",
   *     "total_amount": "-3",
   *     "fee_amount": "-0.25",
   *     "net_amount": "",
   *     "currency": "USD",
   *     "is_payment": "1",
   *     "trxn_id": "abctx123",
   *     "trxn_result_code": "Test a refund with fees",
   *     "status_id": "7",
   *     "payment_processor_id": ""
   *   }
   * }
   *
   * @param array $params
   *
   * @throws \CiviCRM_API3_Exception
   */
  protected function updateContributionRefund($params) {
    $this->checkRequiredParams('updateContributionRefund', ['contribution_id', 'total_amount'], $params);

    $financialTrxn = civicrm_api3('Payment', 'create', $params);

    // order_reference field was introduced in 5.20 but support was not available to save it via Payment.Create
    if (version_compare(\CRM_Utils_System::version(), '5.20', '<')) {
      // Order reference field not available so we do nothing with it.
    }
    else {
      // @fixme We are on 5.20 or above, so have order_reference field available. But it's not yet updated by Payment.Create API
      if (!empty($params['order_reference'])) {
        civicrm_api3('FinancialTrxn', 'create', [
          'id' => $financialTrxn['id'],
          'trxn_id' => $params['trxn_id'],
          'order_reference' => $params['order_reference'] ?? '',
        ]);
      }
    }
  }

  /**
   * Check that required params are present
   *
   * @param string $description
   *   For error logs
   * @param array $requiredParams
   *   Array of params that are required
   * @param array $params
   *   Array of params to check
   */
  protected function checkRequiredParams($description, $requiredParams, $params) {
    foreach ($requiredParams as $required) {
      if (!isset($params[$required])) {
        $this->exception("{$description}: Missing mandatory parameter: {$required}");
      }
    }
  }

  /**
   * Cancel a subscription (recurring contribution)
   * @param array $params
   *
   * @throws \CiviCRM_API3_Exception
   */
  protected function updateRecurCancelled($params) {
    $this->checkRequiredParams('updateRecurCancelled', ['id'], $params);
    civicrm_api3('ContributionRecur', 'cancel', $params);
  }

  /**
   * Update the subscription (recurring contribution) to a successful status
   * @param array $params
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function updateRecurSuccess($params) {
    $this->checkRequiredParams('updateRecurSuccess', ['id'], $params);
    $params['failure_count'] = 0;
    $params['contribution_status_id'] = 'In Progress';

    // Successful charge & more to come.
    civicrm_api3('ContributionRecur', 'create', $params);
  }

  /**
   * Update the subscription (recurring contribution) to a completed status
   * @param array $params
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function updateRecurCompleted($params) {
    $this->checkRequiredParams('updateRecurCompleted', ['id'], $params);
    $params['contribution_status_id'] = 'Completed';

    civicrm_api3('ContributionRecur', 'create', $params);
  }

  /**
   * Update the subscription (recurring contribution) to a failing status
   * @param array $params
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function updateRecurFailed($params) {
    $this->checkRequiredParams('updateRecurFailed', ['id'], $params);

    $failureCount = civicrm_api3('ContributionRecur', 'getvalue', [
      'id' => $params['id'],
      'return' => 'failure_count',
    ]);
    $failureCount++;

    $params['failure_count'] = $failureCount;
    $params['contribution_status_id'] = 'Failed';

    // Change the status of the Recurring and update failed attempts.
    civicrm_api3('ContributionRecur', 'create', $params);
  }

  /**
   * Repeat a contribution (call the Contribution.repeattransaction API)
   *
   * @param string $status
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function repeatContribution($params) {
    $params['is_email_receipt'] = $this->getSendEmailReceipt();
    $params['trxn_id'] = $params['contribution_trxn_id'];

    $contribution = civicrm_api3('Contribution', 'repeattransaction', $params);

    $this->updatePaymentTrxnID($contribution['id'], $params['payment_trxn_id']);
  }

  /**
   * Complete a pending contribution and update associated entities (recur/membership)
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function updateContributionCompleted($params) {
    $this->checkRequiredParams('updateContributionCompleted', ['id', 'trxn_date', 'contribution_trxn_id', 'payment_trxn_id'], $params);
    $params['payment_processor_id'] = $this->_paymentProcessor->getPaymentProcessor()['id'];
    $params['is_email_receipt'] = $this->getSendEmailReceipt();
    $params['trxn_id'] = $params['contribution_trxn_id'];

    $contribution = civicrm_api3('Contribution', 'completetransaction', $params);
    $this->updatePaymentTrxnID($contribution['id'], $params['payment_trxn_id']);
  }

  /**
   * Update a contribution to failed
   * @param array $params ['id', 'receive_date'{, cancel_date, cancel_reason}]
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function updateContributionFailed($params) {
    $this->checkRequiredParams('updateContributionFailed', ['id', 'receive_date', 'payment_trxn_id'], $params);
    $contribution = civicrm_api3('Contribution', 'create', [
      'id' => $params['id'],
      'contribution_status_id' => 'Failed',
      'receive_date' => $params['receive_date'],
    ]);

    $this->updatePaymentTrxnID($contribution['id'], $params['payment_trxn_id']);
  }

  /**
   * Update the payment record so the trxn_id matches the actual transaction from the payment processor as we may have multiple transactions for a single payment (eg. failures, then success).
   * @param int $contributionID
   * @param string $trxnID
   * @param string $orderReference
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function updatePaymentTrxnID($contributionID, $trxnID, $orderReference = '') {
    // @fixme: There needs to be a better way to do this!!
    //   Contribution trxn_id = invoice_id, payment trxn_id = charge_id
    //   but calling completetransaction does not allow us to do that.
    // @fixme: 2! Payment.get does not support the sort option so we can't do limit=1,sort=id DESC
    $payment = civicrm_api3('Payment', 'get', [
      'contribution_id' => $contributionID,
    ]);
    if (empty($payment['count'])) {
      $this->exception('No payments found for contribution ID: ' . $contributionID);
    }
    krsort($payment['values']);
    $paymentID = CRM_Utils_Array::first($payment['values'])['id'];
    civicrm_api3('FinancialTrxn', 'create', [
      'id' => $paymentID,
      'trxn_id' => $trxnID,
      'order_reference' => $orderReference,
    ]);
  }

  /**
   * Log and throw an IPN exception
   *
   * @param string $message
   */
  protected function exception($message) {
    $errorMessage = $this->_paymentProcessor->getPaymentProcessorLabel() . ' Exception: Event: ' . $this->event_type . ' Error: ' . $message;
    Civi::log()->debug($errorMessage);
    http_response_code(400);
    exit(1);
  }
}
