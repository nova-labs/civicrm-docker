<?php

class CRM_CivirulesConditions_Group_GroupType extends CRM_Civirules_Condition {

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
    $group = $triggerData->getEntityData('Group');
    // getting a group with groupType as Array instead of string.
    $group = civicrm_api3('Group', 'getsingle', ['id' => $group['id']]);
    // if no case type, return FALSE
    if (!isset($group['group_type'])) {
      return $isConditionValid;
    }
    // Our assumptions is that we have only one case type id per case.
    switch ($this->conditionParams['operator']) {
      case 0:
        if (in_array( $this->conditionParams['group_type_id'], $group['group_type'])) {
          $isConditionValid = TRUE;
        }
        break;
      case 1:
        if (!in_array( $this->conditionParams['group_type_id'], $group['group_type'])) {
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/group/grouptype', 'rule_condition_id='
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
    $groupTypes = self::getGroupTypes();
    $friendlyText = "";
    if ($this->conditionParams['operator'] == 0) {
      $friendlyText = 'Group Type is one of: ';
    }
    if ($this->conditionParams['operator'] == 1) {
      $friendlyText = 'Group Type is NOT one of: ';
    }
    $groupText = array();
    $groupText[] = $this->conditionParams['group_type_id'];
    if (!empty($groupText)) {
      $friendlyText .= implode(", ", $groupText);
    }
    return $friendlyText;
  }

  public static function getGroupTypes() {
    $return = array();
    $option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'group_type'));
    $groupTypes = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $option_group_id));
    foreach ($groupTypes['values'] as $groupType) {
      $return[$groupType['value']] = $groupType['label'];
    }
    return $return;
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
    return $trigger->doesProvideEntity('Group');
  }

}