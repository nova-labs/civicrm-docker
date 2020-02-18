<?php
/**
 * Class to process action to select settings for privacy options
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 10 Nov 2017
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Contact_Form_CommPref extends CRM_CivirulesActions_Form_Form {
  private $_commPrefs = array();

  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->getCommPrefs();
    $this->add('hidden', 'rule_action_id');
    $this->add('select', 'on_or_off', ts('Switch On or Off'), array('switch ON', 'switch OFF'), TRUE);
    $this->add('select', 'comm_pref', ts('Communication Preference(s)'), $this->_commPrefs, FALSE,
      array('id' => 'comm_pref', 'multiple' => 'multiple', 'class' => 'crm-select2'));

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Method to get the communication preferences from the option group
   */
  private function getCommPrefs() {
    $this->_commPrefs = array();
    try {
      $optionValues = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'preferred_communication_method',
        'is_active' => 1,
        'options' => array('limit' => 0),
      ));
      foreach($optionValues['values'] as $optionValue) {
        $this->_commPrefs[$optionValue['value']] = $optionValue['label'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return;
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleAction->action_params);
    if (isset($data['on_or_off'])) {
      if ($data['on_or_off'] == 1) {
        $defaultValues['on_or_off'] = 0;
      } else {
        $defaultValues['on_or_off'] = 1;
      }
    }
    if (!empty($data['comm_pref'])) {
      $defaultValues['comm_pref'] = $data['comm_pref'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data = array();
    if (isset($this->_submitValues['on_or_off'])) {
      if ($this->_submitValues['on_or_off'] == 1) {
        $data['on_or_off'] = 0;
      } else {
        $data['on_or_off'] = 1;
      }
    }
    if (isset($this->_submitValues['comm_pref'])) {
      $data['comm_pref'] = $this->_submitValues['comm_pref'];
    }
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

}