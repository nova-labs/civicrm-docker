<?php

/**
 * Class for CiviRules SubTypeComparison (extending generic ValueComparison)
 *
 * @author Véronique Gratioulet <veronique.gratioulet@atd-quartmonde.org>
 * @license AGPL-3.0
 */
class CRM_CivirulesConditions_Contact_HasSubtype extends CRM_Civirules_Condition {
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
      case 'in one of':
        $isConditionValid = $this->contactHasOneOfSubTypes($contact_id, $this->conditionParams['subtype_names']);
        break;
      case 'in all of':
        $isConditionValid = $this->contactHasAllSubTypes($contact_id, $this->conditionParams['subtype_names']);
        break;
      case 'not in':
        $isConditionValid = $this->contactHasNotSubType($contact_id, $this->conditionParams['subtype_names']);
        break;
    }
    return $isConditionValid;
  }

  protected function contactHasNotSubType($contact_id, $subtype_names) {
    $isValid = true;

    $subtypes = CRM_Contact_BAO_Contact::getContactSubType($contact_id);
    foreach($subtype_names as $subtype) {
      if (in_array($subtype, $subtypes)) {
        $isValid = false;
      }
    }

    return $isValid;
  }

  protected function contactHasAllSubTypes($contact_id, $subtype_names) {
    $isValid = 0;

    $subtypes = CRM_Contact_BAO_Contact::getContactSubType($contact_id);
    foreach($subtype_names as $subtype) {
      if (in_array($subtype, $subtypes)) {
        $isValid++;
      }
    }

    if (count($subtype_names) == $isValid && count($subtype_names) > 0) {
      return true;
    }

    return false;
  }

  protected function contactHasOneOfSubTypes($contact_id, $subtype_names) {
    $isValid = false;

    $subtypes = CRM_Contact_BAO_Contact::getContactSubType($contact_id);
    foreach($subtype_names as $subtype) {
      if (in_array($subtype, $subtypes)) {
        $isValid = true;
        break;
      }
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact_hassubtype/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $operators = $this->getOperatorOptions();
    $operator = $this->conditionParams['operator'];
    $operatorLabel = ts('unknown');
    if (isset($operators[$operator])) {
      $operatorLabel = $operators[$operator];
    }

    $subtypes = '';
    foreach($this->conditionParams['subtype_names'] as $subtype) {
      if (strlen($subtypes)) {
        $subtypes .= ', ';
      }
      $subtypes .= civicrm_api3('ContactType', 'getvalue', array('return' => 'label', 'name' => $subtype));
    }

    return $operatorLabel.' subtypes ('.$subtypes.')';
  }

  /**
   * Method to get operators
   *
   * @return array
   * @access protected
   */
  public static function getOperatorOptions() {
    return array(
      'in one of' => ts('In one of selected'),
      'in all of' => ts('In all selected'),
      'not in' => ts('Not in selected'),
    );
  }

}