<?php

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Case_AddRole extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $case = $triggerData->getEntityData("Case");
    $params = $this->getActionParameters();
    $api_params['contact_id_a'] = $triggerData->getContactId();
    $api_params['contact_id_b'] = $params['cid'];
    $api_params['relationship_type_id'] = $params['role'];
    $api_params['case_id'] = $case['id'];
    try {
      civicrm_api3('Relationship', 'create', $api_params);
    } catch(\Exception $ex) {
      // Do nothing
    }
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/case/addrole', 'rule_action_id='.$ruleActionId);
  }


  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $roles = self::getCaseRoles();
    $display_name = civicrm_api3('Contact', 'getvalue', ['id' => $params['cid'], 'return' => 'display_name']);
    return E::ts('Add %2 to the case with role <em>%1</em>', array(1 => $roles[$params['role']], 2 =>$display_name));
  }


  /**
   * Validates whether this action works with the selected trigger.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $entities = $trigger->getProvidedEntities();
    return isset($entities['Case']);
  }

  /**
   * Returns a list of possible case roles
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function getCaseRoles() {
    $relationshipTypesApi = civicrm_api3('RelationshipType', 'get', ['options' => ['limit' => 0]]);
    $caseRoles = array();
    foreach($relationshipTypesApi['values'] as $relType) {
      $caseRoles[$relType['id']] = $relType['label_a_b'];
    }
    return $caseRoles;
  }
}
