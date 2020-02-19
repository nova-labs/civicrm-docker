<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_CivirulesCronTrigger_Form_MembershipEndDate extends CRM_CivirulesTrigger_Form_Form {



  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_id');

    $this->add('select', 'membership_type_id', ts('Activity Type'),CRM_Civirules_Utils::getMembershipTypes(), true);
    $this->add('select', 'interval_unit', ts('Interval'),CRM_CivirulesCronTrigger_MembershipEndDate::intervals(), true);
    $this->add('text', 'interval', ts('Interval'), true);
    $this->addRule('interval', ts('Interval should be a numeric value'), 'numeric');


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
    $data = unserialize($this->rule->trigger_params);
    if (!empty($data['membership_type_id'])) {
      $defaultValues['membership_type_id'] = $data['membership_type_id'];
    }
    if (!empty($data['interval_unit'])) {
      $defaultValues['interval_unit'] = $data['interval_unit'];
    }
    if (!empty($data['interval'])) {
      $defaultValues['interval'] = $data['interval'];
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
    $data['membership_type_id'] = $this->_submitValues['membership_type_id'];
    $data['interval_unit'] = $this->_submitValues['interval_unit'];
    $data['interval'] = $this->_submitValues['interval'];
    $this->rule->trigger_params = serialize($data);
    $this->rule->save();

    parent::postProcess();
  }
}