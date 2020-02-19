<?php

class CRM_CivirulesConditions_Membership_Type extends CRM_Civirules_Condition {

  private $conditionParams = array();

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method to determine if the condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $membership = $triggerData->getEntityData('Membership');
    switch ($this->conditionParams['operator']) {
      case 0:
        if ($membership['membership_type_id'] == $this->conditionParams['membership_type_id']) {
          $isConditionValid = TRUE;
        }
      break;
      case 1:
        if ($membership['membership_type_id'] != $this->conditionParams['membership_type_id']) {
          $isConditionValid = TRUE;
        }
      break;
    }
    return $isConditionValid;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/membershiptype', 'rule_condition_id='
      .$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = array(
      'is_active' => 1,
      'options' => array('limit' => 0, 'sort' => "name ASC"),
    );
    try {
      $membershipTypes = civicrm_api3('MembershipType', 'Get', $params);
      $operator = null;
      if ($this->conditionParams['operator'] == 0) {
        $operator = 'equals';
      }
      if ($this->conditionParams['operator'] == 1) {
        $operator = 'is not equal to';
      }
      foreach ($membershipTypes['values'] as $membershipType) {
        if ($membershipType['id'] == $this->conditionParams['membership_type_id']) {
          return "Membership Type ".$operator." ".$membershipType['name'];
        }
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    return '';
  }

  /**
   * This function validates whether this condition works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether a condition is possible in the current setup. E.g. we could have a condition
   * which works on contribution or on contributionRecur then this function could do
   * this kind of validation and return false/true
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return $trigger->doesProvideEntity('Membership');
  }

}