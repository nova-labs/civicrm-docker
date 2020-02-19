<?php
/**
 * Form controller class
 */
class CRM_CivirulesConditions_Form_Contribution_IsRecurring extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $radioOptions = array(
      'is recurring' => ts('is recurring'),
      'is not recurring' => ts('is not recurring'));
    $this->addRadio('test', ts('Contribution') . ': ', $radioOptions);

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
    if (!empty($data['test'])) {
      $defaultValues['test'] = $data['test'];
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
    $data['test'] = $this->_submitValues['test'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    parent::postProcess();
  }

}
