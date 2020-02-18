<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesConditions_Case_CaseStatus extends CRM_Civirules_Condition {

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
    $case = $triggerData->getEntityData('Case');
    $status_id = $case['status_id'];
    switch ($this->conditionParams['operator']) {
      case 0:
        if (in_array($status_id, $this->conditionParams['status_id'])) {
          $isConditionValid = TRUE;
        }
        break;
      case 1:
        if (!in_array($status_id, $this->conditionParams['status_id'])) {
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/case/casestatus', 'rule_condition_id='
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
    $caseStatuses = self::getCaseStatus();
    $friendlyText = "";
    if ($this->conditionParams['operator'] == 0) {
      $friendlyText = 'Case Status is one of: ';
    }
    if ($this->conditionParams['operator'] == 1) {
      $friendlyText = 'Case Status is NOT one of: ';
    }
    $caseText = array();
    foreach ($this->conditionParams['status_id'] as $caseStatusId) {
      $caseText[] = $caseStatuses[$caseStatusId];
    }
    if (!empty($caseText)) {
      $friendlyText .= implode(", ", $caseText);
    }
    return $friendlyText;
  }

  public static function getCaseStatus() {
    $return = array();
    $option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'case_status'));
    $case_statuses = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $option_group_id));
    foreach ($case_statuses['values'] as $case_status) {
      $return[$case_status['value']] = $case_status['label'];
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
    return $trigger->doesProvideEntity('Case');
  }

}