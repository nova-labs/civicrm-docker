<?php

class CRM_CivirulesConditions_Form_Contribution_TotalContributedAmount extends CRM_CivirulesConditions_Form_ValueComparison {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    CRM_CivirulesConditions_Utils_Period::buildQuickForm($this);

    $financial_type_id = $this->add('select', 'financial_type_id', ts('Financial type'), CRM_CivirulesConditions_Contribution_TotalContributedAmount::getFinancialTypes(), false);
    $financial_type_id->setMultiple(true);
    $payment_instrument_id = $this->add('select', 'payment_instrument_id', ts('Payment instrument'), CRM_CivirulesConditions_Contribution_TotalContributedAmount::getPaymentInstruments(), false);
    $payment_instrument_id->setMultiple(true);
    $contribution_status_id = $this->add('select', 'contribution_status_id', ts('Status'), CRM_CivirulesConditions_Contribution_TotalContributedAmount::getContributionStatus(), false);
    $contribution_status_id->setMultiple(true);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();

    $data = unserialize($this->ruleCondition->condition_params);
    $defaultValues = CRM_CivirulesConditions_Utils_Period::setDefaultValues($defaultValues, $data);

    // Backwards compatibility: if contribution status is not set, assume it is the completed status.
    if (!isset($data['contribution_status_id'])) {
      $completed_status_id = civicrm_api3('OptionValue', 'getvalue', array('name' => 'completed', 'return' => 'value', 'option_group_id' => 'contribution_status'));
      $data['contribution_status_id'] = array($completed_status_id);
    }
    if (!isset($data['financial_type_id'])) {
      $data['financial_type_id'] = array();
    }
    if (!isset($data['payment_instrument_id'])) {
      $data['payment_instrument_id'] = array();
    }

    $defaultValues['financial_type_id'] = $data['financial_type_id'];
    $defaultValues['payment_instrument_id'] = $data['payment_instrument_id'];
    $defaultValues['contribution_status_id'] = $data['contribution_status_id'];

    return $defaultValues;
  }

  public function addRules()
  {
    CRM_CivirulesConditions_Utils_Period::addRules($this);
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess()
  {
    $data = unserialize($this->ruleCondition->condition_params);
    $data = CRM_CivirulesConditions_Utils_Period::getConditionParams($this->_submitValues, $data);
    $data['financial_type_id'] = $this->_submitValues['financial_type_id'];
    if (!is_array($data['financial_type_id'])) {
      $data['financial_type_id'] = array();
    }
    $data['payment_instrument_id'] = $this->_submitValues['payment_instrument_id'];
    if (!is_array($data['payment_instrument_id'])) {
      $data['payment_instrument_id'] = array();
    }
    $data['contribution_status_id'] = $this->_submitValues['contribution_status_id'];
    if (!is_array($data['contribution_status_id'])) {
      $data['contribution_status_id'] = array();
    }

    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    parent::postProcess();
  }

}