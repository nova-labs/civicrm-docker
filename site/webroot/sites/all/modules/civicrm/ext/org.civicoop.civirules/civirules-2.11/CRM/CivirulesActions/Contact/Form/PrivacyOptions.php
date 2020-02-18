<?php
/**
 * Class to process action to select settings for privacy options
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 29 Oct 2017
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Contact_Form_PrivacyOptions extends CRM_CivirulesActions_Form_Form {

  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');
    $this->add('select', 'on_or_off', ts('Switch On or Off'), array('switch ON', 'switch OFF'), TRUE);
    $privacyOptions = array(
      'phone' => 'Do not phone',
      'email' => 'Do not email',
      'mail' => 'Do not mail',
      'sms' => 'Do not SMS',
      'trade' => 'Do not trade',
    );
    $this->add('select', 'privacy_options', ts('Privacy Option(s)'), $privacyOptions, FALSE,
      array('id' => 'privacy_options', 'multiple' => 'multiple', 'class' => 'crm-select2'));

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
    $data = unserialize($this->ruleAction->action_params);
    if (isset($data['on_or_off'])) {
      if ($data['on_or_off'] == 1) {
        $defaultValues['on_or_off'] = 0;
      } else {
        $defaultValues['on_or_off'] = 1;
      }
    }
    if (!empty($data['privacy_options'])) {
      $defaultValues['privacy_options'] = $data['privacy_options'];
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
    if (isset($this->_submitValues['privacy_options'])) {
      $data['privacy_options'] = $this->_submitValues['privacy_options'];
    }
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

}