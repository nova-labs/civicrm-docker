<?php
/**
 * Class for CiviRule Condition Campaign is of Type
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 9 Dec 2019
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesConditions_Campaign_Type extends CRM_Civirules_Condition {

  private $_conditionParams = array();

  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/campaign_type/',
      'rule_condition_id=' . $ruleConditionId);
  }

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
   * Method to check if the condition is valid, will check if the contact
   * has an activity of the selected type
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $campaignData = $triggerData->getEntityData('campaign');
    switch ($this->_conditionParams['operator']) {
      case '0':
        if (in_array($campaignData['campaign_type_id'], $this->_conditionParams['campaign_type_id'])) {
          $isConditionValid = TRUE;
        }
        break;
      case '1':
        if (!in_array($campaignData['campaign_type_id'], $this->_conditionParams['campaign_type_id'])) {
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
      $friendlyText = 'Campaign Type is one of: ';
    }
    if ($this->_conditionParams['operator'] == 1) {
      $friendlyText = 'Campaign Type is NOT one of: ';
    }
    $campaignText = [];
    foreach ($this->_conditionParams['campaign_type_id'] as $campaignTypeId) {
      try {
        $campaignText[] = civicrm_api3('OptionValue', 'getvalue', [
          'option_group_id' => 'campaign_type',
          'value' => $campaignTypeId,
          'return' => 'label'
        ]);

      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    if (!empty($campaignText)) {
      $friendlyText .= implode(", ", $campaignText);
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
    return $trigger->doesProvideEntity('Campaign');
  }
}
