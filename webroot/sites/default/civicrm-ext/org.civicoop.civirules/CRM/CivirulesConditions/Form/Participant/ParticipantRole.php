<?php
/**
 * Class for CiviRules Condition Participant Role Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Participant_ParticipantRole extends CRM_CivirulesConditions_Form_Form {

  protected function getRoles() {
    $participantRoleList = civicrm_api3('OptionValue', 'get', array('option_group_id' => "participant_role", 'options' => ['limit' => 0]));
    $roles = array();
    foreach($participantRoleList['values'] as $role) {
      $roles[$role['id']] = $role['label'];
    }
    return $roles;
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $roles = $this->getRoles();
    asort($roles);
    $this->add('select', 'participant_role_id', ts('Participant Role(s)'), $roles, true,
      array('id' => 'participant_role_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
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
    if (!empty($data['participant_role_id'])) {
      $defaultValues['participant_role_id'] = $data['participant_role_id'];
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
    $data['participant_role_id'] = $this->_submitValues['participant_role_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}
