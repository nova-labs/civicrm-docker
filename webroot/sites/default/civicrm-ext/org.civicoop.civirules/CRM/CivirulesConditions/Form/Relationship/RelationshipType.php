<?php


class CRM_CivirulesConditions_Form_Relationship_RelationshipType extends CRM_CivirulesConditions_Form_Form {

  protected function getRelationshipTypes() {
    return CRM_CivirulesConditions_Relationship_RelationshipType::getRelationshipTypes();
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $relationshipTypes = $this->getRelationshipTypes();
    $relationshipTypes[0] = ts('- select -');
    asort($relationshipTypes);
    $this->add('select', 'relationship_type_id', ts('Relationship Type(s)'), $relationshipTypes, true,
      array('id' => 'relationship_type_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
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
    if (!empty($data['relationship_type_id'])) {
      $defaultValues['relationship_type_id'] = $data['relationship_type_id'];
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
    $data['relationship_type_id'] = $this->_submitValues['relationship_type_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}