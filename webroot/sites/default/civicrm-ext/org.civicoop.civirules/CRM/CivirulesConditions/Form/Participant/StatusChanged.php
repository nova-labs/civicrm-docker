<?php
use CRM_Civirules_ExtensionUtil as E;

/**
 * Class for CiviRules Condition Compare old/new participant status form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 Oct 2019
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Participant_StatusChanged extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
    $statuses = CRM_CivirulesConditions_Participant_StatusChanged::getAllParticipantStatus();
    asort($statuses);
    $this->add('select', 'original_status_id', E::ts('Participant Status(es)'), $statuses, TRUE,
      ['id' => 'original_status_ids', 'class' => 'crm-select2']);
    $this->add('select', 'new_status_id', E::ts('Participant Status(es)'), $statuses, TRUE,
      ['id' => 'new_status_ids', 'class' => 'crm-select2']);
    $this->add('select', 'original_operator', E::ts('Operator'), [E::ts('is one of'), E::ts('is NOT one of')], TRUE);
    $this->add('select', 'new_operator', E::ts('Operator'), [E::ts('is one of'), E::ts('is NOT one of')], TRUE);
    $this->addButtons([
      ['type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,],
      ['type' => 'cancel', 'name' => E::ts('Cancel')],
      ]);
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
    if (!empty($data['new_status_id'])) {
      $defaultValues['new_status_id'] = $data['new_status_id'];
    }
    if (!empty($data['original_status_id'])) {
      $defaultValues['original_status_id'] = $data['original_status_id'];
    }
    if (!empty($data['original_operator'])) {
      $defaultValues['original_operator'] = $data['original_operator'];
    }
    if (!empty($data['new_operator'])) {
      $defaultValues['new_operator'] = $data['new_operator'];
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
    $data['original_status_id'] = $this->_submitValues['original_status_id'];
    $data['new_status_id'] = $this->_submitValues['new_status_id'];
    $data['original_operator'] = $this->_submitValues['original_operator'];
    $data['new_operator'] = $this->_submitValues['new_operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}
