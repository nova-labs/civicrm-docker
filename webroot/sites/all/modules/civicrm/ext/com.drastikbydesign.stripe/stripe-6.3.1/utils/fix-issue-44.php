<?php
/**
 * This script will look for stripe recurring contributions with a date <2 Jan
 * 1970 and will try to obtain a proper charge date for them and correct the
 * Contribution records.
 *
 * Drupal users can run it like
 *    drush scr <scriptname>
 *
 * Limitations: it does not correct the financial records. There be dragons.
 *
 * You should have a read through the code and determine if it's what you want
 * before running it.
 *
 * @see https://lab.civicrm.org/extensions/stripe/issues/63
 *
 */
use Stripe\Stripe;
use Stripe\Charge;

exit; // ***REMOVE THIS LINE*** but don't run this until you have understood what it does.

if (php_sapi_name() !== 'cli') {
  // This is NOT to be run from a web browser.
  // Fail with 404 if not called from CLI.
  if (isset($_SERVER['HTTP_PROTOCOL'])) {
    header("$_SERVER[HTTP_PROTOCOL] 404 Not Found");
  }
  exit;
}

civicrm_initialize();
echo "booted ok\n";

$result = civicrm_api3('PaymentProcessor', 'get', [ 'class_name' => 'Payment_Stripe', 'is_active' => 1, ]);
$payment_processors = $result['values'];
if (empty($payment_processors)) {
  echo "Failed to find payment processors\n";
  exit;
}

$dao = CRM_Core_DAO::executeQuery('
  SELECT c.id, c.contact_id, c.total_amount, c.trxn_id, c.contribution_recur_id, cr.payment_processor_id
  FROM civicrm_contribution c
    INNER JOIN civicrm_contribution_recur cr ON c.contribution_recur_id = cr.id
  WHERE c.contribution_status_id = 1 AND c.is_test = 0 AND c.receive_date < 19700102');
while ($dao->fetch()) {
  echo "Contribution $dao->id (contact $dao->contact_id): ";

  $paymentProcessor = $payment_processors[$dao->payment_processor_id] ?? NULL;
  if (!$paymentProcessor) {
    echo "Failed to find a stripe payment processor for recurring contrib $dao->contribution_recur_id\n";
  }
  $processor = new CRM_Core_Payment_Stripe('', civicrm_api3('PaymentProcessor', 'getsingle', ['id' => $paymentProcessor['id']]));
  $processor->setAPIParams();

  try {
    $results = Charge::retrieve(['id' => $dao->trxn_id]);
    //print json_encode($results, JSON_PRETTY_PRINT);
    if (empty($results->created)) {
      echo " Failed to retrieve a charge created date\n";
      continue;
    }
    $d = date('Y-m-d H:i:s', $results->created);
    // Update database
    print "Updating Contribution to date $d\n";
    civicrm_api3('Contribution', 'create', [
      'id' => $dao->id,
      'receive_date' => $d,
    ]);

  } catch (Exception $e) {
    echo "Failed to load Stripe charge $dao->trxn_id \n" . $e->getMessage();
    continue;
  }
}
echo "Done\n";
