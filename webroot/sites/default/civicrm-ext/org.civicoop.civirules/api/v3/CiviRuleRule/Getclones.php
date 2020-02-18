<?php
use CRM_Civirules_ExtensionUtil as E;

/**
 * CiviRuleRule.GetClones API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_civi_rule_rule_Getclones_spec(&$spec) {
  $spec['id']['api.required'] = 1;
  $spec['id']['type']  = CRM_Utils_Type::T_INT;
  $spec['id']['title'] = 'Unique ID  of a rule';
}

/**
 * CiviRuleRule.GetClones API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_rule_rule_Getclones($params) {
  $Id = $params['id'];
  $rule = civicrm_api3('CiviRuleRule', 'getsingle',['id' => $Id]);
  $clones = [];
  if(!$rule['is_active']) {
    $triggerId = $rule['trigger_id'];
    $ruleFormat = CRM_Civirules_Utils::ruleCompareFormat($Id, $triggerId);
    $sql = "select id,label from civirule_rule where trigger_id = %1 and id != %2";
    $dao = CRM_Core_DAO::executeQuery($sql, [
      1 => [$triggerId, 'Integer'],
      2 => [$Id, 'Integer'],
    ]);

    while ($dao->fetch()) {
      $cloneFormat = CRM_Civirules_Utils::ruleCompareFormat($dao->id, $triggerId);
      if ($cloneFormat == $ruleFormat) {
        $clones[$dao->id] = ['id' => $dao->id, 'label' => $dao->label];
      }
    }
  }
  return civicrm_api3_create_success($clones, $params, 'CiviRuleRule', 'getClones');
}
