<?php

class CRM_CivirulesConditions_Event_EventType extends CRM_Civirules_Condition {

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
    $event = $triggerData->getEntityData('Event');
    $event_type_id = $event['event_type_id'];
    switch ($this->conditionParams['operator']) {
      case 0:
        if (in_array($event_type_id, $this->conditionParams['event_type_id'])) {
          $isConditionValid = TRUE;
        }
        break;
      case 1:
        if (!in_array($event_type_id, $this->conditionParams['event_type_id'])) {
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/event_type', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   * @throws Exception
   */
  public function userFriendlyConditionParams() {
    $friendlyText = "";
    if ($this->conditionParams['operator'] == 0) {
      $friendlyText = 'Event Type is one of: ';
    }
    if ($this->conditionParams['operator'] == 1) {
      $friendlyText = 'Event Type is NOT one of: ';
    }
    $typeText = array();
    $eventTypes = civicrm_api3('OptionValue', 'get', array(
      'value' => array('IN' => $this->conditionParams['event_type_id']),
      'option_group_id' => 'event_type',
      'options' => array('limit' => 0)
    ));
    foreach($eventTypes['values'] as $eventType) {
      $typeText[] = $eventType['label'];
    }

    if (!empty($typeText)) {
      $friendlyText .= implode(", ", $typeText);
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
    return $trigger->doesProvideEntity('Event');
  }

}