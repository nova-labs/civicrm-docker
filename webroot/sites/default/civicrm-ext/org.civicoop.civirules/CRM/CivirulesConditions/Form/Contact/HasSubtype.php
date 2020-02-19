<?php
/**
 * Class for CiviRules Condition Contact has subtype
 *
 * @author Véronique Gratioulet <veronique.gratioulet@atd-quartmonde.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Contact_HasSubtype extends CRM_CivirulesConditions_Form_Form {

  /**
   * Method to get subtypes
   *
   * @return array
   * @access protected
   */
  protected function getSubtypes($contactType = null) {
    $contactType = ('Contact' == $contactType ? null : $contactType);
    $all = empty($contactType);
    return CRM_Contact_BAO_ContactType::subTypePairs($contactType, $all, null);
  }

  /**
   * Method to get operators
   *
   * @return array
   * @access protected
   */
  protected function getOperators() {
    return CRM_CivirulesConditions_Contact_HasSubtype::getOperatorOptions();
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
    $object_name = $this->trigger->object_name;

    $subtype = $this->add('select', 'subtype_names', ts('Subtypes'), $this->getSubtypes($object_name), true);
    $subtype->setMultiple(TRUE);
    $this->add('select', 'operator', ts('Operator'), $this->getOperators(), true);

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
    if (!empty($data['subtype_names'])) {
      $defaultValues['subtype_names'] = $data['subtype_names'];
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
    $data['subtype_names'] = $this->_submitValues['subtype_names'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    parent::postProcess();
  }
}