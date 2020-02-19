<?php
/**
 * Class for CiviRule Condition FirstContribution
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesConditions_Activity_Type extends CRM_Civirules_Condition {

  private $conditionParams = array();

  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/activity_type/',
      'rule_condition_id='.$ruleConditionId);
  }

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
   * Method to check if the condition is valid, will check if the contact
   * has an activity of the selected type
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $activityData = $triggerData->getEntityData('Activity');
    $activity = civicrm_api3('Activity', 'getsingle', array(
      'return' => array("activity_type_id"),
      'id' => $activityData['id'],
    ));
    switch ($this->conditionParams['operator']) {
      case 0:
        if (in_array($activity['activity_type_id'], $this->conditionParams['activity_type_id'])) {
          $isConditionValid = TRUE;
        }
        break;
      case 1:
        if (!in_array($activity['activity_type_id'], $this->conditionParams['activity_type_id'])) {
          $isConditionValid = TRUE;
        }
        break;
    }
    return $isConditionValid;
  }
  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $friendlyText = "";
    if ($this->conditionParams['operator'] == 0) {
      $friendlyText = 'Activity Type is one of: ';
    }
    if ($this->conditionParams['operator'] == 1) {
      $friendlyText = 'Activity Type is NOT one of: ';
    }
    $actText = array();
    foreach ($this->conditionParams['activity_type_id'] as $actTypeId) {
      $actText[] = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_type',
        'value' => $actTypeId,
        'return' => 'label'
      ));
    }
    if (!empty($actText)) {
      $friendlyText .= implode(", ", $actText);
    }
    return $friendlyText;
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
    return $trigger->doesProvideEntity('Activity');
  }
}