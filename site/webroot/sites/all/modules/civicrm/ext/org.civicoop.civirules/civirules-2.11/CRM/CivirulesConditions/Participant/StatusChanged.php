<?php
/**
 * Class for CiviRules Participant status changed
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 Oct 2019
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Participant_StatusChanged extends CRM_Civirules_Condition {

  private $_conditionParams = array();

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->_conditionParams = [];
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->_conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method to determine if the condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $participantData = $triggerData->getEntityData('Participant');
    $originalData = $triggerData->getOriginalData();
    if (!isset($originalData['participant_status_id'])) {
      $originalStatus = NULL;
    }
    else {
      $originalStatus = $originalData['participant_status_id'];
    }
    if (!isset($participantData['status_id'])) {
      $newStatus = NULL;
    }
    else {
      $newStatus = $participantData['status_id'];
    }
    $originalCheck = $this->checkCondition($originalStatus, $this->_conditionParams['original_operator'], $this->_conditionParams['original_status_id']);
    $newCheck = $this->checkCondition($newStatus, $this->_conditionParams['new_operator'], $this->_conditionParams['new_status_id']);
    if ($originalCheck && $newCheck) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Method to check status condition
   *
   * @param $statusId
   * @param $operator
   * @param $conditionStatusId
   * @return bool
   */
  private function checkCondition($statusId, $operator, $conditionStatusId) {
    if ($operator == 1) {
      // if not set, then not equal is true
      if (!$statusId) {
        return TRUE;
      }
      else {
        if ($statusId != $conditionStatusId) {
          return TRUE;
        }
      }
    }
    else {
      if ($statusId && $statusId == $conditionStatusId) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Method to get all the participant status types
   *
   * @return array
   */
  public static function getAllParticipantStatus() {
    $result = [];
    try {
      $api = civicrm_api3('ParticipantStatusType', 'get', [
        'return' => ["label"],
        'options' => ['limit' => 0],
        ]);
      foreach ($api['values'] as $statusId => $status) {
        $result[$statusId] = $status['label'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return $result;
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/participant/statuschanged', 'rule_condition_id=' . $ruleConditionId);
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
    $participantStatusList = CRM_CivirulesConditions_Participant_StatusChanged::getAllParticipantStatus();
    $friendlyText = "Original participant status ";
    if ($this->_conditionParams['original_operator'] == 1) {
      $friendlyText .= " is NOT equal " . $participantStatusList[$this->_conditionParams['original_status_id']];
    }
    else {
      $friendlyText .= " is equal " . $participantStatusList[$this->_conditionParams['original_status_id']];
    }
    $friendlyText .= " and new status ";
    if ($this->_conditionParams['new_operator'] == 1) {
      $friendlyText .= " is NOT equal " . $participantStatusList[$this->_conditionParams['new_status_id']];
    }
    else {
      $friendlyText .= " is equal " . $participantStatusList[$this->_conditionParams['new_status_id']];
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
