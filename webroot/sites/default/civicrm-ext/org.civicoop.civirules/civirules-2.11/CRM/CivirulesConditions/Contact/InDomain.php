<?php
/**
 * Class for CiviRules AgeComparison (extending generic ValueComparison)
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_InDomain extends CRM_Civirules_Condition {

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
   * This method returns true or false when an condition is valid or not
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   * @abstract
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = false;
    $contact_id = $triggerData->getContactId();
    switch($this->conditionParams['operator']) {
      case 'in':
        $isConditionValid = $this->contactIsMemberOfDomain($contact_id, $this->conditionParams['domain_id']);
        break;
      case 'not in':
        $isConditionValid = $this->contactIsNotMemberOfDomain($contact_id, $this->conditionParams['domain_id']);
        break;
    }
    return $isConditionValid;
  }

  protected function contactIsNotMemberOfDomain($contact_id, $domain_id) {
    $isValid = true;
    if (self::isContactInDomain($contact_id, $domain_id)) {
      $isValid = false;
    }
    return $isValid;
  }

  protected function contactIsMemberOfDomain($contact_id, $domain_id) {
    $isValid = false;
    if (self::isContactIndomain($contact_id, $domain_id)) {
      $isValid = true;
    }
    return $isValid;
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact_indomain/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $operators = CRM_CivirulesConditions_Contact_InDomain::getOperatorOptions();
    $operator = $this->conditionParams['operator'];
    $operatorLabel = ts('unknown');
    if (isset($operators[$operator])) {
      $operatorLabel = $operators[$operator];
    }

    $domainTitle = self::getDomainName($this->conditionParams['domain_id']);

    return $operatorLabel.' groups ('.$domainTitle.')';
  }

  /**
   * Method to get operators
   *
   * @return array
   * @access protected
   */
  public static function getOperatorOptions() {
    return array(
      'in' => ts('In selected domain'),
      'not in' => ts('Not in selected domain'),
    );
  }

  public static function domains() {
    $domains = [];
    $domainsFound = civicrm_api3('Domain', 'get', []);
    foreach ($domainsFound['values'] as $domainId => $values) {
      $domains[$domainId] = $values['name'];
    }
    return $domains;
  }

  public static function getDomainName($domain_id) {
    return self::domains()[$domain_id];
  }

  public static function isContactIndomain($contact_id, $domain_id) {
    $setting = civicrm_api3('Setting', 'get', ['domain_id' => $domain_id]);
    $group_id = $setting['values'][$domain_id]['domain_group_id'];
    if (empty($group_id)) {
      return TRUE;
    }
    else {
      return CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($contact_id, $group_id);
    }
  }

}
