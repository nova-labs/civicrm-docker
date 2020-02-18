<?php


class CRM_CivirulesConditions_Form_Group_GroupType extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $groupTypes = CRM_CivirulesConditions_Group_GroupType::getGroupTypes();
    $groupTypes[0] = ts('- select -');
    asort($groupTypes);
    $this->add('select', 'group_type_id', ts('Group Type(s)'), $groupTypes, true,
      array('id' => 'group_type_ids','class' => 'crm-select2'));
    $this->add('select', 'operator', ts('Operator'), array('is a', 'is NOT  a'), true);

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
    if (!empty($data['group_type_id'])) {
      $defaultValues['group_type_id'] = $data['group_type_id'];
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
    $data['group_type_id'] = $this->_submitValues['group_type_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}