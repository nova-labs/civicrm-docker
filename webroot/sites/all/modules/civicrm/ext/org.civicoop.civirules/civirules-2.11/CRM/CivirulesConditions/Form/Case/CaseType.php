<?php


class CRM_CivirulesConditions_Form_Case_CaseType extends CRM_CivirulesConditions_Form_Form {

  protected function getCaseTypes() {
    return CRM_CivirulesConditions_Case_CaseType::getCaseTypes();
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $caseTypes = $this->getCaseTypes();
    $caseTypes[0] = ts('- select -');
    asort($caseTypes);
    $this->add('select', 'case_type_id', ts('Case Type(s)'), $caseTypes, true,
      array('id' => 'case_type_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('select', 'operator', ts('Operator'), array('is one of', 'is NOT one of'), true);

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
    if (!empty($data['case_type_id'])) {
      $defaultValues['case_type_id'] = $data['case_type_id'];
    }
    if (!empty($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
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
    $data['case_type_id'] = $this->_submitValues['case_type_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}