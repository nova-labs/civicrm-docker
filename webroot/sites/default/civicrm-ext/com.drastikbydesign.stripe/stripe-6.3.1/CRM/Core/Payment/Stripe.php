<?php
/**
 * https://civicrm.org/licensing
 */

use CRM_Stripe_ExtensionUtil as E;

/**
 * Class CRM_Core_Payment_Stripe
 */
class CRM_Core_Payment_Stripe extends CRM_Core_Payment {

  use CRM_Core_Payment_MJWTrait;

  /**
   *
   * @var string
   */
  const API_VERSION = '2019-12-03';

  /**
   * Mode of operation: live or test.
   *
   * @var object
   */
  protected $_mode = NULL;

  public static function getApiVersion() {
    return self::API_VERSION;
  }
  /**
   * Constructor
   *
   * @param string $mode
   *   The mode of operation: live or test.
   * @param array $paymentProcessor
   *
   * @return void
   */
  public function __construct($mode, $paymentProcessor) {
    $this->_mode = $mode;
    $this->_paymentProcessor = $paymentProcessor;
    $this->_processorName = E::SHORT_NAME;
  }

  /**
   * @param array $paymentProcessor
   *
   * @return string
   */
  public static function getSecretKey($paymentProcessor) {
    return trim(CRM_Utils_Array::value('password', $paymentProcessor));
  }

  /**
   * @param array $paymentProcessor
   *
   * @return string
   */
  public static function getPublicKey($paymentProcessor) {
    return trim(CRM_Utils_Array::value('user_name', $paymentProcessor));
  }

  /**
   * Given a payment processor id, return the public key
   *
   * @param $paymentProcessorId
   *
   * @return string
   */
  public static function getPublicKeyById($paymentProcessorId) {
    try {
      $paymentProcessor = civicrm_api3('PaymentProcessor', 'getsingle', [
        'id' => $paymentProcessorId,
      ]);
      $key = self::getPublicKey($paymentProcessor);
    }
    catch (CiviCRM_API3_Exception $e) {
      return '';
    }
    return $key;
  }

  /**
   * Given a payment processor id, return the secret key
   *
   * @param $paymentProcessorId
   *
   * @return string
   */
  public static function getSecretKeyById($paymentProcessorId) {
    try {
      $paymentProcessor = civicrm_api3('PaymentProcessor', 'getsingle', [
        'id' => $paymentProcessorId,
      ]);
      $key = self::getSecretKey($paymentProcessor);
    }
    catch (CiviCRM_API3_Exception $e) {
      return '';
    }
    return $key;
  }

  /**
   * This function checks to see if we have the right config values.
   *
   * @return null|string
   *   The error message if any.
   */
  public function checkConfig() {
    $error = [];

    if (!empty($error)) {
      return implode('<p>', $error);
    }
    else {
      return NULL;
    }
  }

  /**
   * We can use the smartdebit processor on the backend
   * @return bool
   */
  public function supportsBackOffice() {
    return TRUE;
  }

  /**
   * We can edit smartdebit recurring contributions
   * @return bool
   */
  public function supportsEditRecurringContribution() {
    return FALSE;
  }

  public function supportsRecurring() {
    return TRUE;
  }

  /**
   * Does this payment processor support refund?
   *
   * @return bool
   */
  public function supportsRefund() {
    return TRUE;
  }

  /**
   * Can we set a future recur start date?  Stripe allows this but we don't (yet) support it.
   * @return bool
   */
  public function supportsFutureRecurStartDate() {
    return FALSE;
  }

  /**
   * Is an authorize-capture flow supported.
   *
   * @return bool
   */
  protected function supportsPreApproval() {
    return TRUE;
  }

  /**
   * Get the currency for the transaction.
   *
   * Handle any inconsistency about how it is passed in here.
   *
   * @param array $params
   *
   * @return string
   */
  public function getAmount($params = []): string {
    $amount = number_format((float) $params['amount'] ?? 0.0, CRM_Utils_Money::getCurrencyPrecision($this->getCurrency($params)), '.', '');
    // Stripe amount required in cents.
    $amount = preg_replace('/[^\d]/', '', strval($amount));
    return $amount;
  }

  /**
   * Set API parameters for Stripe (such as identifier, api version, api key)
   */
  public function setAPIParams() {
    // Set plugin info and API credentials.
    \Stripe\Stripe::setAppInfo('CiviCRM', CRM_Utils_System::version(), CRM_Utils_System::baseURL());
    \Stripe\Stripe::setApiKey(self::getSecretKey($this->_paymentProcessor));
    \Stripe\Stripe::setApiVersion(self::getApiVersion());
  }

  /**
   * Handle an error from Stripe API and notify the user
   *
   * @param array $err
   * @param string $bounceURL
   *
   * @return string errorMessage (or statusbounce if URL is specified)
   */
  public function handleErrorNotification($err, $bounceURL = NULL) {
    return self::handleError("{$err['type']} {$err['code']}", $err['message'], $bounceURL);
  }

  /**
   * Stripe exceptions contain a json object in the body "error". This function extracts and returns that as an array.
   * @param String $op
   * @param Exception $e
   * @param Boolean $log
   *
   * @return array $err
   */
  public static function parseStripeException($op, $e, $log = FALSE) {
    $body = $e->getJsonBody();
    if ($log) {
      Civi::log()->debug("Stripe_Error {$op}: " . print_r($body, TRUE));
    }
    $err = $body['error'];
    if (!isset($err['code'])) {
      // A "fake" error code
      $err['code'] = 9000;
    }
    return $err;
  }

  /**
   * Create or update a Stripe Plan
   *
   * @param array $params
   * @param integer $amount
   *
   * @return \Stripe\Plan
   */
  public function createPlan($params, $amount) {
    $currency = $this->getCurrency($params);
    $planId = "every-{$params['frequency_interval']}-{$params['frequency_unit']}-{$amount}-" . strtolower($currency);

    if ($this->_paymentProcessor['is_test']) {
      $planId .= '-test';
    }

    // Try and retrieve existing plan from Stripe
    // If this fails, we'll create a new one
    try {
      $plan = \Stripe\Plan::retrieve($planId);
    }
    catch (Stripe\Error\InvalidRequest $e) {
      $err = self::parseStripeException('plan_retrieve', $e, FALSE);
      if ($err['code'] === 'resource_missing') {
        $formatted_amount = CRM_Utils_Money::formatLocaleNumericRoundedByCurrency(($amount / 100), $currency);
        $productName = "CiviCRM " . (isset($params['membership_name']) ? $params['membership_name'] . ' ' : '') . "every {$params['frequency_interval']} {$params['frequency_unit']}(s) {$currency}{$formatted_amount}";
        if ($this->_paymentProcessor['is_test']) {
          $productName .= '-test';
        }
        $product = \Stripe\Product::create([
          "name" => $productName,
          "type" => "service"
        ]);
        // Create a new Plan.
        $stripePlan = [
          'amount' => $amount,
          'interval' => $params['frequency_unit'],
          'product' => $product->id,
          'currency' => $currency,
          'id' => $planId,
          'interval_count' => $params['frequency_interval'],
        ];
        $plan = \Stripe\Plan::create($stripePlan);
      }
    }

    return $plan;
  }
  /**
   * Override CRM_Core_Payment function
   *
   * @return array
   */
  public function getPaymentFormFields() {
    return [];
  }

  /**
   * Return an array of all the details about the fields potentially required for payment fields.
   *
   * Only those determined by getPaymentFormFields will actually be assigned to the form
   *
   * @return array
   *   field metadata
   */
  public function getPaymentFormFieldsMetadata() {
    return [];
  }

  /**
   * Get form metadata for billing address fields.
   *
   * @param int $billingLocationID
   *
   * @return array
   *    Array of metadata for address fields.
   */
  public function getBillingAddressFieldsMetadata($billingLocationID = NULL) {
    $metadata = parent::getBillingAddressFieldsMetadata($billingLocationID);
    if (!$billingLocationID) {
      // Note that although the billing id is passed around the forms the idea that it would be anything other than
      // the result of the function below doesn't seem to have eventuated.
      // So taking this as a param is possibly something to be removed in favour of the standard default.
      $billingLocationID = CRM_Core_BAO_LocationType::getBilling();
    }

    // Stripe does not require some of the billing fields but users may still choose to fill them in.
    $nonRequiredBillingFields = ["billing_state_province_id-{$billingLocationID}", "billing_postal_code-{$billingLocationID}"];
    foreach ($nonRequiredBillingFields as $fieldName) {
      if (!empty($metadata[$fieldName]['is_required'])) {
        $metadata[$fieldName]['is_required'] = FALSE;
      }
    }

    return $metadata;
  }

  /**
   * Set default values when loading the (payment) form
   *
   * @param \CRM_Core_Form $form
   */
  public function buildForm(&$form) {
    // Don't use \Civi::resources()->addScriptFile etc as they often don't work on AJAX loaded forms (eg. participant backend registration)
    $jsVars = [
      'id' => $form->_paymentProcessor['id'],
      'currency' => $this->getDefaultCurrencyForForm($form),
      'billingAddressID' => CRM_Core_BAO_LocationType::getBilling(),
      'publishableKey' => CRM_Core_Payment_Stripe::getPublicKeyById($form->_paymentProcessor['id']),
      'jsDebug' => (boolean) \Civi::settings()->get('stripe_jsdebug'),
      'paymentProcessorTypeID' => $form->_paymentProcessor['payment_processor_type_id'],
    ];
    \Civi::resources()->addVars(E::SHORT_NAME, $jsVars);
    // Assign to smarty so we can add via Card.tpl for drupal webform because addVars doesn't work in that context
    $form->assign('stripeJSVars', $jsVars);

    // Enable JS validation for forms so we only (submit) create a paymentIntent when the form has all fields validated.
    $form->assign('isJsValidate', TRUE);

    // Add help and javascript
    CRM_Core_Region::instance('billing-block')->add(
      ['template' => 'CRM/Core/Payment/Stripe/Card.tpl', 'weight' => -1]);
    // Add CSS via region (it won't load on drupal webform if added via \Civi::resources()->addStyleFile)

    $min = ((boolean) \Civi::settings()->get('stripe_jsdebug')) ? '' : '.min';
    CRM_Core_Region::instance('billing-block')->add([
      'styleUrl' => \Civi::resources()->getUrl(E::LONG_NAME, "css/elements{$min}.css"),
      'weight' => -1,
    ]);
    CRM_Core_Region::instance('billing-block')->add([
      'scriptUrl' => \Civi::resources()->getUrl(E::LONG_NAME, "js/civicrm_stripe{$min}.js"),
    ]);
  }

  /**
   * Function to action pre-approval if supported
   *
   * @param array $params
   *   Parameters from the form
   *
   * This function returns an array which should contain
   *   - pre_approval_parameters (this will be stored on the calling form & available later)
   *   - redirect_url (if set the browser will be redirected to this.
   *
   * @return array
   */
  public function doPreApproval(&$params) {
    $preApprovalParams['paymentIntentID'] = CRM_Utils_Request::retrieve('paymentIntentID', 'String');
    $preApprovalParams['paymentMethodID'] = CRM_Utils_Request::retrieve('paymentMethodID', 'String');
    return ['pre_approval_parameters' => $preApprovalParams];
  }

  /**
   * Get any details that may be available to the payment processor due to an approval process having happened.
   *
   * In some cases the browser is redirected to enter details on a processor site. Some details may be available as a
   * result.
   *
   * @param array $storedDetails
   *
   * @return array
   */
  public function getPreApprovalDetails($storedDetails) {
    return $storedDetails;
  }

  /**
   * Process payment
   * Submit a payment using Stripe's PHP API:
   * https://stripe.com/docs/api?lang=php
   * Payment processors should set payment_status_id.
   *
   * @param array $params
   *   Assoc array of input parameters for this transaction.
   * @param string $component
   *
   * @return array
   *   Result array
   *
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public function doPayment(&$params, $component = 'contribute') {
    $params = $this->beginDoPayment($params);
    if ((CRM_Utils_Array::value('is_recur', $params) && $this->getRecurringContributionId($params))
        || $this->isPaymentForEventAdditionalParticipants()) {
      $params = $this->getTokenParameter('paymentMethodID', $params, TRUE);
    }
    else {
      $params = $this->getTokenParameter('paymentIntentID', $params, TRUE);
    }

    $pendingStatusId = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');

    $this->setAPIParams();

    // Get proper entry URL for returning on error.
    if (!(array_key_exists('qfKey', $params))) {
      // Probably not called from a civicrm form (e.g. webform) -
      // will return error object to original api caller.
      $params['stripe_error_url'] = NULL;
    }
    else {
      $qfKey = $params['qfKey'];
      $parsedUrl = parse_url($params['entryURL']);
      $urlPath = substr($parsedUrl['path'], 1);
      $query = $parsedUrl['query'];
      if (strpos($query, '_qf_Main_display=1') === FALSE) {
        $query .= '&_qf_Main_display=1';
      }
      if (strpos($query, 'qfKey=') === FALSE) {
        $query .= "&qfKey={$qfKey}";
      }
      $params['stripe_error_url'] = CRM_Utils_System::url($urlPath, $query, FALSE, NULL, FALSE);
    }
    $amount = self::getAmount($params);

    $contactId = $this->getContactId($params);
    $email = $this->getBillingEmail($params, $contactId);

    // See if we already have a stripe customer
    $customerParams = [
      'contact_id' => $contactId,
      'processor_id' => $this->_paymentProcessor['id'],
      'email' => $email,
      // Include this to allow redirect within session on payment failure
      'stripe_error_url' => $params['stripe_error_url'],
    ];

    // Get the Stripe Customer:
    //   1. Look for an existing customer.
    //   2. If no customer (or a deleted customer found), create a new one.
    //   3. If existing customer found, update the metadata that Stripe holds for this customer.
    $stripeCustomerId = CRM_Stripe_Customer::find($customerParams);
    // Customer not in civicrm database.  Create a new Customer in Stripe.
    if (!isset($stripeCustomerId)) {
      $stripeCustomer = CRM_Stripe_Customer::create($customerParams, $this);
    }
    else {
      // Customer was found in civicrm database, fetch from Stripe.
      try {
        $stripeCustomer = \Stripe\Customer::retrieve($stripeCustomerId);
      } catch (Exception $e) {
        $err = self::parseStripeException('retrieve_customer', $e, FALSE);
        $errorMessage = $this->handleErrorNotification($err, $params['stripe_error_url']);
        throw new \Civi\Payment\Exception\PaymentProcessorException('Failed to retrieve Stripe Customer: ' . $errorMessage);
      }

      if ($stripeCustomer->isDeleted()) {
        // Customer doesn't exist, create a new one
        CRM_Stripe_Customer::delete($customerParams);
        try {
          $stripeCustomer = CRM_Stripe_Customer::create($customerParams, $this);
        } catch (Exception $e) {
          // We still failed to create a customer
          $errorMessage = $this->handleErrorNotification($stripeCustomer, $params['stripe_error_url']);
          throw new \Civi\Payment\Exception\PaymentProcessorException('Failed to create Stripe Customer: ' . $errorMessage);
        }
      }
      else {
        CRM_Stripe_Customer::updateMetadata($customerParams, $this, $stripeCustomer->id);
      }
    }

    // Prepare the charge array, minus Customer/Card details.
    if (empty($params['description'])) {
      $params['description'] = E::ts('Contribution: %1', [1 => $this->getPaymentProcessorLabel()]);
    }

    // Handle recurring payments in doRecurPayment().
    if (CRM_Utils_Array::value('is_recur', $params) && $this->getRecurringContributionId($params)) {
      // We're processing a recurring payment - for recurring payments we first saved a paymentMethod via the browser js.
      // Now we use that paymentMethod to setup a stripe subscription and take the first payment.
      // This is where we save the customer card
      // @todo For a recurring payment we have to save the card. For a single payment we'd like to develop the
      //   save card functionality but should not save by default as the customer has not agreed.
      $paymentMethod = \Stripe\PaymentMethod::retrieve($params['paymentMethodID']);
      $paymentMethod->attach(['customer' => $stripeCustomer->id]);
      $stripeCustomer = \Stripe\Customer::retrieve($stripeCustomer->id);

      // We set payment status as pending because the IPN will set it as completed / failed
      $params['payment_status_id'] = $pendingStatusId;
      return $this->doRecurPayment($params, $amount, $stripeCustomer, $paymentMethod);
    }
    elseif ($this->isPaymentForEventAdditionalParticipants()) {
      // We're processing an event registration for multiple participants - because we did not know
      //   the amount until now we process via a saved paymentMethod.
      $paymentMethod = \Stripe\PaymentMethod::retrieve($params['paymentMethodID']);
      $paymentMethod->attach(['customer' => $stripeCustomer->id]);
      $stripeCustomer = \Stripe\Customer::retrieve($stripeCustomer->id);
      $intent = \Stripe\PaymentIntent::create([
        'payment_method' => $params['paymentMethodID'],
        'customer' => $stripeCustomer->id,
        'amount' => $amount,
        'currency' => $this->getCurrency($params),
        'confirmation_method' => 'automatic',
        'capture_method' => 'manual',
        // authorize the amount but don't take from card yet
        'setup_future_usage' => 'off_session',
        // Setup the card to be saved and used later
        'confirm' => true,
      ]);
      $params['paymentIntentID'] = $intent->id;
    }

    $intentParams = [
      'customer' => $stripeCustomer->id,
      'description' => $this->getDescription($params, 'description'),
    ];
    $intentParams['statement_descriptor_suffix'] = $this->getDescription($params, 'statement_descriptor_suffix');
    $intentParams['statement_descriptor'] = $this->getDescription($params, 'statement_descriptor');

    // This is where we actually charge the customer
    try {
      $intent = \Stripe\PaymentIntent::retrieve($params['paymentIntentID']);
      if ($intent->amount != $this->getAmount($params)) {
        $intentParams['amount'] = $this->getAmount($params);
      }
      $intent = \Stripe\PaymentIntent::update($intent->id, $intentParams);
    }
    catch (Exception $e) {
      $this->handleError($e->getCode(), $e->getMessage(), $params['stripe_error_url']);
    }

    list($params, $newParams) = $this->processPaymentIntent($params, $intent);

    // For a single charge there is no stripe invoice, we set OrderID to the ChargeID.
    if (empty($this->getPaymentProcessorOrderID())) {
      $this->setPaymentProcessorOrderID($this->getPaymentProcessorTrxnID());
    }

    // For contribution workflow we have a contributionId so we can set parameters directly.
    // For events/membership workflow we have to return the parameters and they might get set...
    return $this->endDoPayment($params, $newParams);
  }

  /**
   * @return bool
   */
  private function isPaymentForEventAdditionalParticipants() {
    return !empty($this->getParam('additional_participants'));
  }

  /**
   * Submit a recurring payment using Stripe's PHP API:
   * https://stripe.com/docs/api?lang=php
   *
   * @param array $params
   *   Assoc array of input parameters for this transaction.
   * @param int $amount
   *   Transaction amount in USD cents.
   * @param \Stripe\Customer $stripeCustomer
   *   Stripe customer object generated by Stripe API.
   * @param \Stripe\PaymentMethod $stripePaymentMethod
   *
   * @return array
   *   The result in a nice formatted array (or an error object).
   *
   * @throws \CiviCRM_API3_Exception
   * @throws \CRM_Core_Exception
   */
  public function doRecurPayment($params, $amount, $stripeCustomer, $stripePaymentMethod) {
    $required = NULL;
    if (empty($this->getRecurringContributionId($params))) {
      $required = 'contributionRecurID';
    }
    if (!isset($params['frequency_unit'])) {
      $required = 'frequency_unit';
    }
    if ($required) {
      Civi::log()->error('Stripe doRecurPayment: Missing mandatory parameter: ' . $required);
      throw new CRM_Core_Exception('Stripe doRecurPayment: Missing mandatory parameter: ' . $required);
    }

    // Make sure frequency_interval is set (default to 1 if not)
    empty($params['frequency_interval']) ? $params['frequency_interval'] = 1 : NULL;

    // Create the stripe plan
    $planId = self::createPlan($params, $amount);

    // Attach the Subscription to the Stripe Customer.
    $subscriptionParams = [
      'prorate' => FALSE,
      'plan' => $planId,
      'default_payment_method' => $stripePaymentMethod,
      'metadata' => ['Description' => $params['description']],
      'expand' => ['latest_invoice.payment_intent'],
    ];

    // Create the stripe subscription for the customer
    $stripeSubscription = $stripeCustomer->subscriptions->create($subscriptionParams);
    $this->setPaymentProcessorSubscriptionID($stripeSubscription->id);

    $recurParams = [
      'id' =>     $this->getRecurringContributionId($params),
      'trxn_id' => $this->getPaymentProcessorSubscriptionID(),
      // FIXME processor_id is deprecated as it is not guaranteed to be unique, but currently (CiviCRM 5.9)
      //  it is required by cancelSubscription (where it is called subscription_id)
      'processor_id' => $this->getPaymentProcessorSubscriptionID(),
      'auto_renew' => 1,
      'cycle_day' => date('d'),
      'next_sched_contribution_date' => $this->calculateNextScheduledDate($params),
    ];
    if (!empty($params['installments'])) {
      // We set an end date if installments > 0
      if (empty($params['start_date'])) {
        $params['start_date'] = date('YmdHis');
      }
      if ($params['installments']) {
        $recurParams['end_date'] = $this->calculateEndDate($params);
        $recurParams['installments'] = $params['installments'];
      }
    }

    // Hook to allow modifying recurring contribution params
    CRM_Stripe_Hook::updateRecurringContribution($recurParams);
    // Update the recurring payment
    civicrm_api3('ContributionRecur', 'create', $recurParams);

    // Get the paymentIntent for the latest invoice
    $intent = $stripeSubscription->latest_invoice['payment_intent'];

    list($params, $newParams) = $this->processPaymentIntent($params, $intent);

    // Set the orderID (trxn_id) to the invoice ID
    // The IPN will change it to the charge_id
    $this->setPaymentProcessorOrderID($stripeSubscription->latest_invoice['id']);

    return $this->endDoPayment($params, $newParams);
  }

  /**
   * This performs the processing and recording of the paymentIntent for both recurring and non-recurring payments
   * @param array $params
   * @param \Stripe\PaymentIntent $intent
   *
   * @return array [$params, $newParams]
   */
  private function processPaymentIntent($params, $intent) {
    $contactId = $this->getContactId($params);
    $email = $this->getBillingEmail($params, $contactId);
    $newParams = [];

    try {
      if ($intent->status === 'requires_confirmation') {
        $intent->confirm();
      }

      switch ($intent->status) {
        case 'requires_capture':
          $intent->capture();
          // Return fees & net amount for Civi reporting.
          $stripeCharge = $intent->charges->data[0];
          try {
            $stripeBalanceTransaction = \Stripe\BalanceTransaction::retrieve($stripeCharge->balance_transaction);
          }
          catch (Exception $e) {
            $err = self::parseStripeException('retrieve_balance_transaction', $e, FALSE);
            $errorMessage = $this->handleErrorNotification($err, $params['stripe_error_url']);
            throw new \Civi\Payment\Exception\PaymentProcessorException('Failed to retrieve Stripe Balance Transaction: ' . $errorMessage);
          }
          $newParams['fee_amount'] = $stripeBalanceTransaction->fee / 100;
          $newParams['net_amount'] = $stripeBalanceTransaction->net / 100;
          // Success!
          // Set the desired contribution status which will be set later (do not set on the contribution here!)
          $params['contribution_status_id'] = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');
          // Transaction ID is always stripe Charge ID.
          $this->setPaymentProcessorTrxnID($stripeCharge->id);

        case 'requires_action':
          // We fall through to this in requires_capture / requires_action so we always set a receipt_email
          if ((boolean) \Civi::settings()->get('stripe_oneoffreceipt')) {
            // Send a receipt from Stripe - we have to set the receipt_email after the charge has been captured,
            //   as the customer receives an email as soon as receipt_email is updated and would receive two if we updated before capture.
            \Stripe\PaymentIntent::update($intent->id, ['receipt_email' => $email]);
          }
          break;
      }
    }
    catch (Exception $e) {
      $this->handleError($e->getCode(), $e->getMessage(), $params['stripe_error_url']);
    }

    // Update the paymentIntent in the CiviCRM database for later tracking
    $intentParams = [
      'paymentintent_id' => $intent->id,
      'payment_processor_id' => $this->_paymentProcessor['id'],
      'status' => $intent->status,
      'contribution_id' => $this->getContributionId($params),
      'description' => $this->getDescription($params, 'description'),
      'identifier' => $params['qfKey'],
      'contact_id' => $this->getContactId($params),
    ];
    if (empty($intentParams['contribution_id'])) {
      $intentParams['flags'][] = 'NC';
    }
    CRM_Stripe_BAO_StripePaymentintent::create($intentParams);

    return [$params, $newParams];
  }

  /**
   * Submit a refund payment
   *
   * @param array $params
   *   Assoc array of input parameters for this transaction.
   *
   * @return array
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public function doRefund(&$params) {
    $requiredParams = ['trxn_id', 'amount'];
    foreach ($requiredParams as $required) {
      if (!isset($params[$required])) {
        $message = 'Stripe doRefund: Missing mandatory parameter: ' . $required;
        Civi::log()->error($message);
        Throw new \Civi\Payment\Exception\PaymentProcessorException($message);
      }
    }
    $refundParams = [
      'charge' => $params['trxn_id'],
    ];
    $refundParams['amount'] = $this->getAmount($params);
    try {
      $refund = \Stripe\Refund::create($refundParams);
    }
    catch (Exception $e) {
      $this->handleError($e->getCode(), $e->getMessage());
      Throw new \Civi\Payment\Exception\PaymentProcessorException($e->getMessage());
    }

    switch ($refund->status) {
      case 'pending':
        $refundStatus = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');
        break;

      case 'succeeded':
        $refundStatus = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');
        break;

      case 'failed':
        $refundStatus = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Failed');
        break;

      case 'canceled':
        $refundStatus = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Cancelled');
        break;
    }

    $refundParams = [
      'refund_trxn_id' => $refund->id,
      'refund_status_id' => $refundStatus,
      'processor_result' => $refund->jsonSerialize(),
    ];
    return $refundParams;
  }

  /**
   * Get a description field
   * @param array $params
   * @param string $type
   *   One of description, statement_descriptor, statement_descriptor_suffix
   *
   * @return string
   */
  private function getDescription($params, $type = 'description') {
    if (!isset(\Civi::$statics[__CLASS__]['description']['contact_contribution'])) {
      \Civi::$statics[__CLASS__]['description']['contact_contribution'] = $this->getContactId($params) . '-' . ($this->getContributionId($params) ?: 'XX');
    }
    switch ($type) {
      case 'statement_descriptor':
        return substr(\Civi::$statics[__CLASS__]['description']['contact_contribution'] . " " . $params['description'], 0, 22);

      case 'statement_descriptor_suffix':
        return \Civi::$statics[__CLASS__]['description']['contact_contribution'] . " " . substr($params['description'],0,7);

      default:
        return "{$params['description']} " . \Civi::$statics[__CLASS__]['description']['contact_contribution'] . " #" . CRM_Utils_Array::value('invoiceID', $params);
    }
  }

  /**
   * Calculate the end_date for a recurring contribution based on the number of installments
   * @param $params
   *
   * @return string
   * @throws \CRM_Core_Exception
   */
  public function calculateEndDate($params) {
    $requiredParams = ['start_date', 'installments', 'frequency_interval', 'frequency_unit'];
    foreach ($requiredParams as $required) {
      if (!isset($params[$required])) {
        $message = 'Stripe calculateEndDate: Missing mandatory parameter: ' . $required;
        Civi::log()->error($message);
        throw new CRM_Core_Exception($message);
      }
    }

    switch ($params['frequency_unit']) {
      case 'day':
        $frequencyUnit = 'D';
        break;

      case 'week':
        $frequencyUnit = 'W';
        break;

      case 'month':
        $frequencyUnit = 'M';
        break;

      case 'year':
        $frequencyUnit = 'Y';
        break;
    }

    $numberOfUnits = $params['installments'] * $params['frequency_interval'];
    $endDate = new DateTime($params['start_date']);
    $endDate->add(new DateInterval("P{$numberOfUnits}{$frequencyUnit}"));
    return $endDate->format('Ymd') . '235959';
  }

  /**
   * Calculate the end_date for a recurring contribution based on the number of installments
   * @param $params
   *
   * @return string
   * @throws \CRM_Core_Exception
   */
  public function calculateNextScheduledDate($params) {
    $requiredParams = ['frequency_interval', 'frequency_unit'];
    foreach ($requiredParams as $required) {
      if (!isset($params[$required])) {
        $message = 'Stripe calculateNextScheduledDate: Missing mandatory parameter: ' . $required;
        Civi::log()->error($message);
        throw new CRM_Core_Exception($message);
      }
    }
    if (empty($params['start_date']) && empty($params['next_sched_contribution_date'])) {
      $startDate = date('YmdHis');
    }
    elseif (!empty($params['next_sched_contribution_date'])) {
      if ($params['next_sched_contribution_date'] < date('YmdHis')) {
        $startDate = $params['next_sched_contribution_date'];
      }
    }
    else {
      $startDate = $params['start_date'];
    }

    switch ($params['frequency_unit']) {
      case 'day':
        $frequencyUnit = 'D';
        break;

      case 'week':
        $frequencyUnit = 'W';
        break;

      case 'month':
        $frequencyUnit = 'M';
        break;

      case 'year':
        $frequencyUnit = 'Y';
        break;
    }

    $numberOfUnits = $params['frequency_interval'];
    $endDate = new DateTime($startDate);
    $endDate->add(new DateInterval("P{$numberOfUnits}{$frequencyUnit}"));
    return $endDate->format('Ymd');
  }

  /**
   * Default payment instrument validation.
   *
   * Implement the usual Luhn algorithm via a static function in the CRM_Core_Payment_Form if it's a credit card
   * Not a static function, because I need to check for payment_type.
   *
   * @param array $values
   * @param array $errors
   */
  public function validatePaymentInstrument($values, &$errors) {
    // Use $_POST here and not $values - for webform fields are not set in $values, but are in $_POST
    CRM_Core_Form::validateMandatoryFields($this->getMandatoryFields(), $_POST, $errors);
  }

  /**
   * @param string $message
   * @param array $params
   *
   * @return bool|object
   */
  public function cancelSubscription(&$message = '', $params = []) {
    $this->setAPIParams();

    try {
      $contributionRecur = civicrm_api3('ContributionRecur', 'getsingle', [
        'id' => $this->getRecurringContributionId($params),
      ]);
    }
    catch (Exception $e) {
      return FALSE;
    }
    if (empty($contributionRecur['trxn_id'])) {
      CRM_Core_Session::setStatus(E::ts('The recurring contribution cannot be cancelled (No reference (trxn_id) found).'), 'Smart Debit', 'error');
      return FALSE;
    }

    try {
      $subscription = \Stripe\Subscription::retrieve($contributionRecur['trxn_id']);
      if (!$subscription->isDeleted()) {
        $subscription->cancel();
      }
    }
    catch (Exception $e) {
      $errorMessage = 'Could not delete Stripe subscription: ' . $e->getMessage();
      CRM_Core_Session::setStatus($errorMessage, 'Stripe', 'error');
      Civi::log()->debug($errorMessage);
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Process incoming payment notification (IPN).
   *
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   * @throws \Stripe\Error\Api
   */
  public static function handlePaymentNotification() {
    $data_raw = file_get_contents("php://input");
    $data = json_decode($data_raw);
    $ipnClass = new CRM_Core_Payment_StripeIPN($data);
    if ($ipnClass->main()) {
      http_response_code(200);
    }
  }

}
