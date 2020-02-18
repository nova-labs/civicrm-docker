<?php
/**
 * Class for CiviRules Contact Has Been in Group Form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 April 2018
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Contact_HasBeenInGroup extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
    $groupList = array();
    try {
      $groups = civicrm_api3('Group', 'get', array(
        'is_active' => 1,
        'options' => array('limit' => 0),
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      $groups = array();
    }
    foreach ($groups['values'] as $group) {
      $groupList[$group['id']] = $group['title'];
    }
    asort($groupList);
    $this->add('select', 'group_id', ts('Group(s)'), $groupList, TRUE,
      array('id' => 'group_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('select', 'operator', ts('Operator'), array('has been in', 'has NEVER been in'), TRUE);
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
    if (isset($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
    }
    if (!empty($data['group_id'])) {
      $defaultValues['group_id'] = $data['group_id'];
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
    $data['group_id'] = $this->_submitValues['group_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    parent::postProcess();
  }
}