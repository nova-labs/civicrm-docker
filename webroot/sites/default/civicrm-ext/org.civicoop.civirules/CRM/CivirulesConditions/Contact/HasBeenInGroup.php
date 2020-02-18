<?php
/**
 * Class for CiviRules Condition Has (Never) Been In Group
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 25 April 2018
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_HasBeenInGroup extends CRM_Civirules_Condition {

  private $_conditionParams = array();

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
    // base query
    $return = FALSE;
    $query = 'SELECT COUNT(*) FROM civicrm_subscription_history WHERE contact_id = %1 AND status = %2';
    $queryParams = array(
      1 => array($triggerData->getContactId(), 'Integer'),
      2 => array('Added', 'String'),
      );
    $index = 2;
    $groupIds = array();
    // add group_ids
    foreach ($this->_conditionParams['group_id'] as $groupId) {
      $index++;
      $groupIds[] = '%'.$index;
      $queryParams[$index] = array($groupId, 'Integer');
    }
    if (!empty($groupIds)) {
      $query .= ' AND group_id IN (' . implode(', ', $groupIds) . ')';
      $count = CRM_Core_DAO::singleValueQuery($query, $queryParams);
      // determine if valid taking operator into account (0 = has been in, 1 = never has been in)
      switch ($this->_conditionParams['operator']) {
        case 0:
          if ($count > 0) {
            $return = TRUE;
          }
          else {
            $return = FALSE;
          }
          break;
        case 1:
          if ($count > 0) {
            $return = FALSE;
          }
          else {
            $return = TRUE;
          }
      }
    }
    return $return;
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact/hasbeeningroup/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $text = '';
    $operatorLabels = array('has been in', 'has NEVER been in');
    if (isset($this->_conditionParams['operator'])) {
      $text = $operatorLabels[$this->_conditionParams['operator']];
    }
    $groupNames = array();
    foreach ($this->_conditionParams['group_id'] as $groupId) {
      try {
        $groupNames[] = civicrm_api3('Group', 'getvalue', array(
          'id' => $groupId,
          'return' => 'title',
        ));
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    if (!empty($groupNames)) {
      $text .= ': '.implode('; ', $groupNames);
    }
    else {
      $text .= ': '.implode('; ', $this->_conditionParams['group_id']);

    }
    return $text;
  }

}