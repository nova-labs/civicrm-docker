<?php
/**
 * https://civicrm.org/licensing
 */

require_once 'stripe.civix.php';
require_once __DIR__.'/vendor/autoload.php';

use CRM_Stripe_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config().
 */
function stripe_civicrm_config(&$config) {
  _stripe_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
function stripe_civicrm_xmlMenu(&$files) {
  _stripe_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
function stripe_civicrm_install() {
  _stripe_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
function stripe_civicrm_uninstall() {
  _stripe_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable().
 */
function stripe_civicrm_enable() {
  _stripe_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable().
 */
function stripe_civicrm_disable() {
  return _stripe_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 */
function stripe_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _stripe_civix_civicrm_upgrade($op, $queue);
}


/**
 * Implementation of hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function stripe_civicrm_managed(&$entities) {
  _stripe_civix_civicrm_managed($entities);
}


/**
 * Implements hook_civicrm_entityTypes().
 */
function stripe_civicrm_entityTypes(&$entityTypes) {
  _stripe_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 */
function stripe_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _stripe_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_alterContent
 *
 * Adding civicrm_stripe.js in a way that works for webforms and (some) Civi forms.
 * hook_civicrm_buildForm is not called for webforms
 *
 * @return void
 */
function stripe_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
  /* Adding stripe js:
   * - Webforms don't get scripts added by hook_civicrm_buildForm so we have to user alterContent
   * - (Webforms still call buildForm and it looks like they are added but they are not,
   *   which is why we check for $object instanceof CRM_Financial_Form_Payment here to ensure that
   *   Webforms always have scripts added).
   * - Almost all forms have context = 'form' and a paymentprocessor object.
   * - Membership backend form is a 'page' and has a _isPaymentProcessor=true flag.
   *
   */
  if (($context == 'form' && !empty($object->_paymentProcessor['class_name']))
    || (($context == 'page') && !empty($object->_isPaymentProcessor))) {
    if (!isset(\Civi::$statics[E::LONG_NAME]['stripeJSLoaded']) || $object instanceof CRM_Financial_Form_Payment) {
      $min = ((boolean) \Civi::settings()->get('stripe_jsdebug')) ? '' : '.min';
      $stripeJSURL = \Civi::resources()->getUrl(E::LONG_NAME, "js/civicrm_stripe{$min}.js");
      $content .= "<script src='{$stripeJSURL}'></script>";
      \Civi::$statics[E::LONG_NAME]['stripeJSLoaded'] = TRUE;
    }
  }
}

/**
 * Add stripe.js to forms, to generate stripe token
 * hook_civicrm_alterContent is not called for all forms (eg. CRM_Contribute_Form_Contribution on backend)
 *
 * @param string $formName
 * @param \CRM_Core_Form $form
 *
 * @throws \CRM_Core_Exception
 */
function stripe_civicrm_buildForm($formName, &$form) {
  // Don't load stripe js on ajax forms
  if (CRM_Utils_Request::retrieveValue('snippet', 'String') === 'json') {
    return;
  }

  // Load stripe.js on all civi forms per stripe requirements
  if (!isset(\Civi::$statics[E::LONG_NAME]['stripeJSLoaded'])) {
    \Civi::resources()->addScriptUrl('https://js.stripe.com/v3');
    \Civi::$statics[E::LONG_NAME]['stripeJSLoaded'] = TRUE;
  }

  switch ($formName) {
    case 'CRM_Contribute_Form_Contribution_ThankYou':
    case 'CRM_Event_Form_Registration_ThankYou':
      \Civi::resources()->addScriptFile(E::LONG_NAME, 'js/civicrmStripeConfirm.js');

      // This is a fairly nasty way of matching and retrieving our paymentIntent as it is no longer available.
      $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String');
      if (!empty($qfKey)) {
        try {
          $paymentIntent = civicrm_api3('StripePaymentintent', 'getsingle', [
            'return' => [
              'paymentintent_id',
              'status',
              'contribution_id'
            ],
            'identifier' => $qfKey
          ]);
        }
        catch (Exception $e) {
          // If we can't find a paymentIntent assume it was not a Stripe transaction and don't load Stripe vars
          // This could happen for various reasons (eg. amount = 0).
          return;
        }
      }

      if (empty($paymentIntent['contribution_id'])) {
        // If we now have a contribution ID try and update it so we can cross-reference the paymentIntent
        $contributionId = $form->getVar('_values')['contributionId'];
        if (!empty($contributionId)) {
          civicrm_api3('StripePaymentintent', 'create', [
            'id' => $paymentIntent['id'],
            'contribution_id' => $contributionId
          ]);
        }
      }

      /** @var \CRM_Core_Payment_Stripe $paymentProcessor */
      $paymentProcessor = \Civi\Payment\System::singleton()->getById($form->_paymentProcessor['id']);
      $paymentProcessor->setAPIParams();
      try {
        $intent = \Stripe\PaymentIntent::retrieve($paymentIntent['paymentintent_id']);
        if (!in_array($intent->status, ['succeeded', 'cancelled'])) {
          // We need the confirmation_method to decide whether to use handleCardAction (manual) or handleCardPayment (automatic) on the js side
          $jsVars = [
            'id' => $form->_paymentProcessor['id'],
            'paymentIntentID' => $paymentIntent['paymentintent_id'],
            'paymentIntentStatus' => $intent->status,
            'paymentIntentMethod' => $intent->confirmation_method,
            'publishableKey' => CRM_Core_Payment_Stripe::getPublicKeyById($form->_paymentProcessor['id']),
            'jsDebug' => (boolean) \Civi::settings()->get('stripe_jsdebug'),
          ];
          \Civi::resources()->addVars(E::SHORT_NAME, $jsVars);
        }
      }
      catch (Exception $e) {
        // Do nothing, we won't attempt further stripe processing
      }
      break;
  }
}

/**
 * Implements hook_civicrm_check().
 *
 * @throws \CiviCRM_API3_Exception
 */
function stripe_civicrm_check(&$messages) {
  CRM_Stripe_Webhook::check($messages);
  CRM_Stripe_Check::checkRequirements($messages);
}

/**
 * Implements hook_civicrm_navigationMenu().
 */
function stripe_civicrm_navigationMenu(&$menu) {
  _stripe_civix_insert_navigation_menu($menu, 'Administer/CiviContribute', array(
    'label' => E::ts('Stripe Settings'),
    'name' => 'stripe_settings',
    'url' => 'civicrm/admin/setting/stripe',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _stripe_civix_navigationMenu($menu);
}
