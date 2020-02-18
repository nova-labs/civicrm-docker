<?php
/**
 * https://civicrm.org/licensing
 */

/**
 * This api cleans up old data / tables in Stripe.
 *
 * This api should only be used if you have read the documentation and understand what it does.
 */

/**
 * Stripe.Cleanup API specification
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 */
function _civicrm_api3_stripe_Cleanup_spec(&$spec) {
  $spec['confirm'] = [
    'api.required' => TRUE,
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'title' => 'Set this to TRUE to execute this API function',
  ];
}

/**
 * Stripe.Cleanup API
 *
 * @param $params
 *
 * @return array
 * @throws \CiviCRM_API3_Exception
 */
function civicrm_api3_stripe_Cleanup($params) {
  if (empty($params['confirm'])) {
    throw new CiviCRM_API3_Exception('You must set the parameter "confirm" to run the Stripe.cleanup API');
  }

  CRM_Core_DAO::executeQuery('DROP TABLE IF EXISTS civicrm_stripe_plans');
  CRM_Core_DAO::executeQuery('DROP TABLE IF EXISTS civicrm_stripe_subscriptions');
  if (CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_stripe_customers', 'is_live')) {
    CRM_Core_DAO::executeQuery('ALTER TABLE `civicrm_stripe_customers` DROP COLUMN `is_live`');
  }
  if (CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_stripe_customers', 'email')) {
    CRM_Core_DAO::executeQuery('ALTER TABLE `civicrm_stripe_customers` DROP COLUMN `email`');
  }

  return civicrm_api3_create_success([]);
}
