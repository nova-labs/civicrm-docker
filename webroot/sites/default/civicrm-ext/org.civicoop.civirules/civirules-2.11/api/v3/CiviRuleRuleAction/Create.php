<?php
use CRM_Civirules_ExtensionUtil as E;

/**
 * CiviRuleRuleAction.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_civi_rule_rule_action_Create_spec(&$spec) {
 $spec['rule_id']['api.required'] = 1;
 $spec['action_id']['api.required'] = 1;
 $spec['action_params']['api.required'] = 0;
}

/**
 * CiviRuleRuleAction.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_rule_rule_action_Create($params) {
  $returnValues = CRM_Civirules_BAO_RuleAction::add($params);
  $keyedReturnValues = [$returnValues['id']=>$returnValues];
  return civicrm_api3_create_success($keyedReturnValues, $params, 'CiviRuleRuleAction', 'Create');
}
