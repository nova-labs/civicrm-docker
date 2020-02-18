<?php
/**
 * Class for CiviRule Condition FirstContribution
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 May 2018
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesConditions_Activity_Campaign extends CRM_Civirules_Condition {

  private $_conditionParams = array();

  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/activity/campaign',
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
    $this->_conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->_conditionParams = unserialize($this->ruleCondition['condition_params']);
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
    if (!isset($activityData['campaign_id'])) {
      try {
        $campaignId = civicrm_api3('Activity', 'getvalue', array(
          'id' => $activityData['id'],
          'return' => 'campaign_id',
        ));
      }
      catch (CiviCRM_API3_Exception $ex) {
        $campaignId = NULL;
      }
    }
    else {
      $campaignId = $activityData['campaign_id'];
    }
    switch ($this->_conditionParams['operator']) {
      case 0:
        if (in_array($campaignId, $this->_conditionParams['campaign_id'])) {
          $isConditionValid = TRUE;
        }
        break;
      case 1:
        if (!in_array($campaignId, $this->_conditionParams['campaign_id'])) {
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
    if ($this->_conditionParams['operator'] == 0) {
      $friendlyText = 'Campaign is one of: ';
    }
    if ($this->_conditionParams['operator'] == 1) {
      $friendlyText = 'Campaign is NOT one of: ';
    }
    $campaignTitles = array();
    foreach ($this->_conditionParams['campaign_id'] as $campaignId) {
      try {
        $campaignTitles[] = civicrm_api3('Campaign', 'getvalue', array(
          'id' => $campaignId,
          'return' => 'title'
        ));
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    if (!empty($campaignTitles)) {
      $friendlyText .= implode(", ", $campaignTitles);
    }
    else {
      $friendlyText .= implode(', ' , $this->_conditionParams['campaign_id']);
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