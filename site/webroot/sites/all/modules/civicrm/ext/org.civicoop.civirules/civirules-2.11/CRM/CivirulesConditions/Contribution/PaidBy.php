<?php

class CRM_CivirulesConditions_Contribution_PaidBy extends CRM_Civirules_Condition {

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
   * Method to determine if the condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */

  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $contribution = $triggerData->getEntityData('Contribution');
    $paymentInstrumentIds = explode(',', $this->_conditionParams['payment_instrument_id']);
    switch ($this->_conditionParams['operator']) {
      case 0:
        if (in_array($contribution['payment_instrument_id'], $paymentInstrumentIds)) {
          $isConditionValid = TRUE;
        }
      break;
      case 1:
        if (!in_array($contribution['payment_instrument_id'], $paymentInstrumentIds)) {
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contribution_paidby/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $operator = null;
    if ($this->_conditionParams['operator'] == 0) {
      $operator = 'is one of';
    }
    if ($this->_conditionParams['operator'] == 1) {
      $operator = 'is not one of';
    }
    $paymentNames = [];
    $paymentInstrumentIds = explode(',', $this->_conditionParams['payment_instrument_id']);
    try {
      $apiParams = [
        'sequential' => 1,
        'return' => ["label"],
        'option_group_id' => "payment_instrument",
        'options' => ['limit' => 0],
        'is_active' => 1,
        'value' => ['IN' => $paymentInstrumentIds],
      ];
      $paymentInstruments = civicrm_api3('OptionValue', 'get', $apiParams);
      foreach ($paymentInstruments['values'] as $paymentInstrument) {
        $paymentNames[] = $paymentInstrument['label'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
      $logMessage = ts('Could not find payment_instruments in ') . __METHOD__
        . ts(', error from API OptionValue get: ') . $ex->getMessage();
      $civiVersion = CRM_Civirules_Utils::getCiviVersion();
      if ($civiVersion < 4.7) {
        CRM_Core_Error::debug_log_message($logMessage);
      }
      else {
        Civi::log()->debug($logMessage);
      }
    }
    if (!empty($paymentNames)) {
      return 'Paid by ' . $operator . ' ' . implode(', ', $paymentNames);
    }
    return '';
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
    return $trigger->doesProvideEntity('Contribution');
  }

}