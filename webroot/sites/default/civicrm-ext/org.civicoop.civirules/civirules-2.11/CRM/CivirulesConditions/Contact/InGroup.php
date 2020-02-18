<?php
/**
 * Class for CiviRules AgeComparison (extending generic ValueComparison)
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_InGroup extends CRM_Civirules_Condition {

  private $conditionParams = [];

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = [];
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * This method returns true or false when an condition is valid or not
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   * @abstract
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $contact_id = $triggerData->getContactId();
    $checkGroupIds = $this->conditionParams['group_ids'];
    if (!isset($this->conditionParams['check_group_tree'])) {
      $this->conditionParams['check_group_tree'] = FALSE;
    }
    // if check_group_tree, add child groups to checkGroupIds (link https://lab.civicrm.org/extensions/civirules/issues/18)
    if ($this->conditionParams['check_group_tree']) {
      $children = CRM_Contact_BAO_GroupNesting::getDescendentGroupIds($checkGroupIds);
      foreach ($children as $child) {
        if (!in_array($child, $checkGroupIds)) {
          $checkGroupIds[] = $child;
        }
      }
    }
    switch($this->conditionParams['operator']) {
      case 'in one of':
        $isConditionValid = $this->contactIsMemberOfOneGroup($contact_id, $checkGroupIds);
        break;
      case 'in all of':
        $isConditionValid = $this->contactIsMemberOfAllGroups($contact_id, $checkGroupIds);
        break;
      case 'not in':
        $isConditionValid = $this->contactIsNotMemberOfGroup($contact_id, $checkGroupIds);
        break;
    }
    return $isConditionValid;
  }

  protected function contactIsNotMemberOfGroup($contact_id, $group_ids) {
    $isValid = TRUE;
    foreach($group_ids as $gid) {
      if (CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($contact_id, $gid)) {
        $isValid = FALSE;
        break;
      }
    }
    return $isValid;
  }

  protected function contactIsMemberOfOneGroup($contact_id, $group_ids) {
    $isValid = FALSE;
    foreach($group_ids as $gid) {
      if (CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($contact_id, $gid)) {
        $isValid = TRUE;
        break;
      }
    }
    return $isValid;
  }

  protected function contactIsMemberOfAllGroups($contact_id, $group_ids) {
    $isValid = 0;
    foreach($group_ids as $gid) {
      if (CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($contact_id, $gid)) {
        $isValid++;
      }
    }
    if (count($group_ids) == $isValid && count($group_ids) > 0) {
      return TRUE;
    }
    return FALSE;
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact_ingroup/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $operators = CRM_CivirulesConditions_Contact_InGroup::getOperatorOptions();
    $operator = $this->conditionParams['operator'];
    $operatorLabel = ts('unknown');
    if (isset($operators[$operator])) {
      $operatorLabel = $operators[$operator];
    }

    $groups = '';
    foreach($this->conditionParams['group_ids'] as $gid) {
      if (strlen($groups)) {
        $groups .= ', ';
      }
      try {
        $groups .= civicrm_api3('Group', 'getvalue', [
          'return' => 'title',
          'id' => $gid
        ]);
      } catch (Exception $e) {
        // Do nothing.
      }
    }
    $friendlyTxt = $operatorLabel . ' groups (' . $groups . ')';
    if ($this->conditionParams['check_group_tree']) {
      $friendlyTxt .= ' (also checking child group membership)';
    }
    return $friendlyTxt;
  }

  /**
   * Method to get operators
   *
   * @return array
   * @access protected
   */
  public static function getOperatorOptions() {
    return [
      'in one of' => ts('In one of selected'),
      'in all of' => ts('In all selected'),
      'not in' => ts('Not in selected'),
    ];
  }

}
