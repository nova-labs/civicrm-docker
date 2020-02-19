<?php
use CRM_Civirules_ExtensionUtil as E;

/**
 * CiviRuleRule.Clone API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_civi_rule_rule_Clone_spec(&$spec) {
  $spec['id']['api.required'] = 1;
  $spec['id']['type']  = CRM_Utils_Type::T_INT;
  $spec['id']['title'] = 'Unique ID of the rule to be cloned';
}

/**
 * CiviRuleRule.Clone API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_rule_rule_Clone($params) {
  $Id = $params['id'];
  $rule = civicrm_api3('CiviRuleRule', 'getsingle',['id' => $Id]);
  $userId = CRM_Core_Session::singleton()->getLoggedInContactID();
  $cloneRule = CRM_Civirules_BAO_Rule::add([
    'name' => substr('clone_of_'.$rule['name'], 0, 80),
    'label' => substr('Clone of '.$rule['label'], 0, 128),
    'trigger_id' => $rule['trigger_id'],
    'trigger_params' => $rule['trigger_params'],
    // a clone is disabled by default
    'is_active' => 0,
    'description' => $rule['description'],
    'help_text' => $rule['help_text'],
    'created_date' => date('Ymd'),
    'created_user_id' => $userId
  ]);
  $cloneId = $cloneRule['id'];

  $ruleConditions = CRM_Civirules_BAO_RuleCondition::getValues(['rule_id' => $Id ]);
  foreach ($ruleConditions as $ruleCondition) {
    $newCondition = [];
    $newCondition['rule_id'] = $cloneId;
    $newCondition['condition_id'] = $ruleCondition['condition_id'];
    $newCondition['is_active'] = $ruleCondition['is_active'];
    if(isset($ruleCondition['condition_link'])){
      $newCondition['condition_link'] = $ruleCondition['condition_link'];
    }
    if(isset($ruleCondition['condition_params'])){
      $newCondition['condition_params'] = $ruleCondition['condition_params'];
    }
    CRM_Civirules_BAO_RuleCondition::add($newCondition);
  }

  $ruleActions = CRM_Civirules_BAO_RuleAction::getValues(['rule_id' => $Id]);
  foreach($ruleActions as $ruleAction) {
    $newAction = [];
    $newAction['rule_id'] = $cloneId;
    $newAction['action_id'] = $ruleAction['action_id'];
    $newAction['ignore_condition_with_delay'] = $ruleAction['ignore_condition_with_delay'];
    $newAction['is_active'] = $ruleAction['is_active'];
    if(isset($ruleAction['action_params'])) {
      $newAction['action_params'] = $ruleAction['action_params'];
    }
    if(isset($ruleAction['delay'])) {
      $newAction['delay'] = $ruleAction['delay'];
    }
    CRM_Civirules_BAO_RuleAction::add($newAction);
  }

  $ruleTags = CRM_Civirules_BAO_RuleTag::getValues(['rule_id' => $Id]);
  foreach($ruleTags as $ruleTag) {
    CRM_Civirules_BAO_RuleTag::add([
      'rule_id' => $cloneId,
      'rule_tag_id' => $ruleTag['rule_tag_id'],
    ]);
  }

  $resultValues = [
    'id' => $Id,
    'clone_id' => $cloneId,
  ];
  return civicrm_api3_create_success($resultValues, $params, 'CiviRuleRule', 'clone');
}
