<?php

class CRM_CivirulesConditions_Form_Case_CaseActivity extends CRM_CivirulesConditions_Form_Form {

  protected function getCaseStatus() {
    return CRM_CivirulesConditions_Case_CaseStatus::getCaseStatus();
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $this->add('text', 'days_inactive', ts('Number of days'), array(), true);

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
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
    if (!empty($data['days_inactive'])) {
      $defaultValues['days_inactive'] = $data['days_inactive'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['days_inactive'] = $this->_submitValues['days_inactive'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}
