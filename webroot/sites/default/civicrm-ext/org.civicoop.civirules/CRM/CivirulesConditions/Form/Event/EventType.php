<?php
/**
 * Class for CiviRules Condition Event Type Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Event_EventType extends CRM_CivirulesConditions_Form_Form {

  protected function getEventTypes() {
    $eventTypeList = civicrm_api3('OptionValue', 'get', array('option_group_id' => "event_type", 'options' => ['limit' => 0]));
    $eventTypes = array();
    foreach($eventTypeList['values'] as $eventType) {
      $eventTypes[$eventType['id']] = $eventType['label'];
    }
    return $eventTypes;
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $eventTypes = $this->getEventTypes();
    asort($eventTypes);
    $this->add('select', 'event_type_id', ts('Event Type(s)'), $eventTypes, true,
      array('id' => 'event_type_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
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
    if (!empty($data['event_type_id'])) {
      $defaultValues['event_type_id'] = $data['event_type_id'];
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
    $data['event_type_id'] = $this->_submitValues['event_type_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}
