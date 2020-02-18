<?php
/**
 * Class for CiviRules Contribution Thank You Date Form
 *
 * @author John Kirk (CiviCooP) <john@civifirst.com>
 * @license AGPL-3.0
 */

class CRM_CivirulesActions_User_Form_DisplayMessage extends CRM_CivirulesActions_Form_Form {

  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');
    $messageTypes = array('alert' => 'Alert', 'info' => 'Info', 'success' => 'Success', 'error' => 'Error',);
    $this->add('text', 'title', ts('Message title: '));
    $this->add('text', 'message', ts('Message: '));
    $this->addRadio('type', ts('Type of message: '), $messageTypes);
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
    $defaultValues['rule_action_id'] = $this->ruleActionId;
    if (!empty($this->ruleAction->action_params)) {
      $data = unserialize($this->ruleAction->action_params);
    }
    if (!empty($data['title'])) {
      $defaultValues['title'] = $data['title'];
    }
    if (!empty($data['message'])) {
      $defaultValues['message'] = $data['message'];
    }
    if (!empty($data['type'])) {
      $defaultValues['type'] = $data['type'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['title'] = $this->_submitValues['title'];
    $data['message'] = $this->_submitValues['message'];
    $data['type'] = $this->_submitValues['type'];
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }
}