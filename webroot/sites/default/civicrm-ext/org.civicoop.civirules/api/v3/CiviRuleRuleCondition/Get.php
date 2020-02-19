<?php
use CRM_Civirules_ExtensionUtil as E;

/**
 * CiviRuleRuleCondition.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_civi_rule_rule_condition_Get_spec(&$spec) {
}

/**
 * CiviRuleRuleCondition.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_rule_rule_condition_Get($params) {
  $returnValues = CRM_Civirules_BAO_RuleCondition::getValues($params);
  return civicrm_api3_create_success($returnValues, $params, 'CiviRuleRule', 'Get');
}
