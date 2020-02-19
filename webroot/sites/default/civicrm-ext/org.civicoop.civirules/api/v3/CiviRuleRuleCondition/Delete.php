<?php
use CRM_Civirules_ExtensionUtil as E;

/**
 * CiviRuleRuleAction.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_civi_rule_rule_condition_Delete_spec(&$spec) {
  $spec['id']['api.required'] = 1;
}

/**
 * CiviRuleRuleCondition.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_civi_rule_rule_condition_Delete($params) {
  $id = $params['id'];
  CRM_Civirules_BAO_RuleCondition::deleteWithId($id);
  return civicrm_api3_create_success(1, $params, 'CiviRuleRuleCondition', 'delete');
}
