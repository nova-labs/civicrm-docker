<?php
/**
 * Class for CiviRules Group Contact Action Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesActions_Membership_Form_Add extends CRM_CivirulesActions_Form_Form {

  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');

    $membershipTypeOptions = array('' => ts('-- please select --'));
    $membershipTypeApi = civicrm_api3('MembershipType', 'get', array('option' => array('limit' => 0)));
    foreach($membershipTypeApi['values'] as $membershipType) {
      $membershipTypeOptions[$membershipType['id']] = $membershipType['name'];
    }

    $this->add('select', 'membership_type_id', ts('Membership type'), $membershipTypeOptions, true, array(
      'class' => 'select2 huge',
    ));

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
    if (!empty($data['membership_type_id'])) {
      $defaultValues['membership_type_id'] = $data['membership_type_id'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['membership_type_id'] = $this->_submitValues['membership_type_id'];
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

}
