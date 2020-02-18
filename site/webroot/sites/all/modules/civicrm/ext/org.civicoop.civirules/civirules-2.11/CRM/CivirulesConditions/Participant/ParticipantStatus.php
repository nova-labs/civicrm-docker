<?php

class CRM_CivirulesConditions_Participant_ParticipantStatus extends CRM_Civirules_Condition {

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
    $participant = $triggerData->getEntityData('Participant');
    $participant_status_id = $participant['participant_status_id'];
    switch ($this->conditionParams['operator']) {
      case 0:
        if (in_array($participant_status_id, $this->conditionParams['participant_status_id'])) {
          $isConditionValid = TRUE;
        }
        break;
      case 1:
        if (!in_array($participant_status_id, $this->conditionParams['participant_status_id'])) {
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/participant_status', 'rule_condition_id='.$ruleConditionId);
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
      $friendlyText = 'Participant Status is one of: ';
    }
    if ($this->conditionParams['operator'] == 1) {
      $friendlyText = 'Participant Status is NOT one of: ';
    }
    $statusText = array();
    $participantStatus = civicrm_api3('ParticipantStatusType', 'get', array(
      'id' => array('IN' => $this->conditionParams['participant_status_id']),
      'option_group_id' => 'participant_status',
      'options' => array('limit' => 0)
    ));
    foreach($participantStatus['values'] as $status) {
      $statusText[] = $status['label'];
    }

    if (!empty($statusText)) {
      $friendlyText .= implode(", ", $statusText);
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
    return $trigger->doesProvideEntity('Participant');
  }

}