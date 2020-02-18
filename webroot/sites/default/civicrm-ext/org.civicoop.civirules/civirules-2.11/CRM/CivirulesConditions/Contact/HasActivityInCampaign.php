<?php
/**
 * Class for CiviRules Condition Has Activity of Type(s) in Campaign(s)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 25 April 2018
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_HasActivityInCampaign extends CRM_Civirules_Condition {

  private $_conditionParams = array();
  private $_query = NULL;
  private $_index = NULL;
  private $_queryParams = array();

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
   * Method to determine if condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $return = FALSE;
    $contactId = $triggerData->getContactId();
    if ($contactId) {
      $this->_query = 'SELECT COUNT(*) 
      FROM civicrm_activity AS act
      JOIN civicrm_activity_contact AS contact ON act.id = contact.activity_id AND contact.record_type_id = %1 
      WHERE act.is_test = %2 AND contact.contact_id = %3';
      $this->_queryParams = array(
        1 => array(3, 'Integer'),
        2 => array(0, 'Integer'),
        3 => array($contactId, 'Integer'),
      );
      $this->_index = 3;
      // add activity type and campaign clause(s)
      $this->addWhereClauses('activity_type_id');
      $this->addWhereClauses('campaign_id');
      // only check if there are actually activity types and campaigns in the condition parameters
      if ($this->_index > 3) {
        $count = CRM_Core_DAO::singleValueQuery($this->_query, $this->_queryParams);
        if ($count > 0) {
          $return = TRUE;
        }
      }
    }
    return $return;
  }

  /**
   * Method to set the where clauses
   *
   * @param $fieldName
   */
  private function addWhereClauses($fieldName) {
    $fieldIds = array();
    foreach ($this->_conditionParams[$fieldName] as $fieldValue) {
      $this->_index++;
      $fieldIds[] = '%'.$this->_index;
      $this->_queryParams[$this->_index] = array($fieldValue, 'Integer');
    }
    if (!empty($fieldIds)) {
      $this->_query .= ' AND act.' . $fieldName . ' IN (' . implode(', ', $fieldIds) . ')';
    }
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact/hasactivityincampaign/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $activityTypeLabels = array();
    foreach ($this->_conditionParams['activity_type_id'] as $activityTypeId) {
      try {
        $activityTypeLabels[] = civicrm_api3('OptionValue', 'getvalue', array(
          'option_group_id' => 'activity_type',
          'value' => $activityTypeId,
          'return' => 'label',
        ));
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    if (!empty($activityTypeLabels)) {
      $text = ts('has activities of type(s)') . ': ' . implode('; ', $activityTypeLabels);
    }
    else {
      $text = ts('has activities of type(s)') . ': ' . implode('; ', $this->_conditionParams['activity_type_id']);

    }
    $campaignTitles = array();
    foreach ($this->_conditionParams['campaign_id'] as $campaignId) {
      try {
        $campaignTitles[] = civicrm_api3('Campaign', 'getvalue', array(
          'id' => $campaignId,
          'return' => 'title',
        ));
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    if (!empty($campaignTitles)) {
      $text .= ts(' in campaign(s)') . ': ' . implode('; ', $campaignTitles);
    }
    else {
      $text .= ts(' in campaign(s)') . ': ' . implode('; ', $this->_conditionParams['campaign_id']);

    }
    return $text;
  }

}