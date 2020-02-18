<?php
/**
 * Class for CiviRules Condition xthContribution Form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 12 Nov 2018
 * @funded by Amnesty International Vlaanderen
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Contribution_xthContribution extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
    $this->add('select', 'operator', ts('Operator'), CRM_Civirules_Utils::getGenericComparisonOperatorOptions(), TRUE);
    $this->add('select', 'financial_type', ts('of Financial Type(s)'), CRM_Civirules_Utils::getFinancialTypes(), TRUE,
      array('id' => 'financial_type_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('text', 'number_contributions', ts('Number of Contributions'), array(), TRUE);
    $this->addRule('number_contributions','Number of Contributions must be a whole number','numeric');
    $this->addRule('number_contributions','Number of Contributions must be a whole number','nopunctuation');
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
    if (!empty($data['number_contributions'])) {
      $defaultValues['number_contributions'] = $data['number_contributions'];
    }
    if (!empty($data['financial_type'])) {
      $defaultValues['financial_type'] = $data['financial_type'];
    }
    if (!empty($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
    }
    return $defaultValues;
  }

  /**
   * Function to add validation condition rules (overrides parent function)
   *
   * @access public
   */
  public function addRules() {
    $this->addFormRule(array('CRM_CivirulesConditions_Form_Contribution_xthContribution', 'validateCompareZero'));
  }

  /**
   * Method to validate if the operator works with value zero
   *
   * @param $fields
   * @return array|bool
   */
  public static function validateCompareZero($fields) {
    // zero in number only allowed if operator greater than
    if (isset($fields['operator']) && isset($fields['number_contributions'])) {
      if ($fields['number_contributions'] == 0 && $fields['operator'] != 1) {
        $errors['number_contributions'] = ts('Comparing value 0 with anything but greater than makes no sense');
        return $errors;
      }
    }
    return TRUE;
  }


  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['number_contributions'] = $this->_submitValues['number_contributions'];
    $data['operator'] = $this->_submitValues['operator'];
    $data['financial_type'] = $this->_submitValues['financial_type'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}