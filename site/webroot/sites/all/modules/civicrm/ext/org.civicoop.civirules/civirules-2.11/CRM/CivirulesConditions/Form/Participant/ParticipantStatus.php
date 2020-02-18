<?php
/**
 * Class for CiviRules Condition Participant Status Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Participant_ParticipantStatus extends CRM_CivirulesConditions_Form_Form {

  protected function getParticipantStatuses() {
    $participantStatusList = civicrm_api3('ParticipantStatusType', 'get', ['options' => ['limit' => 0]]);
    $statuses = array();
    foreach($participantStatusList['values'] as $status) {
      $statuses[$status['id']] = $status['label'];
    }
    return $statuses;
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $statuses = $this->getParticipantStatuses();
    asort($statuses);
    $this->add('select', 'participant_status_id', ts('Participant Status(es)'), $statuses, true,
      array('id' => 'participant_status_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
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
    if (!empty($data['participant_status_id'])) {
      $defaultValues['participant_status_id'] = $data['participant_status_id'];
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
    $data['participant_status_id'] = $this->_submitValues['participant_status_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}
