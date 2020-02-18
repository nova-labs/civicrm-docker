<?php
/**
 * Class for CiviRules Case Set Status Action Form
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Case_Form_AddRole extends CRM_CivirulesActions_Form_Form {

  /**
   * Build the form.
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');

    $this->add('select', 'role', E::ts('Case role'), CRM_CivirulesActions_Case_AddRole::getCaseRoles(), true, array('class' => 'crm-select2 huge'));
    $this->addEntityRef('cid', E::ts('Contact'), [], true);

    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel')
      )
    ));
  }


  /**
   * Set default values.
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleAction->action_params);
    $defaultValues['role'] = empty($data['role']) ? '' : $data['role'];
    $defaultValues['cid'] = empty($data['cid']) ? '' : $data['cid'];
    return $defaultValues;
  }


  /**
   * Process form data after submitting
   */
  public function postProcess() {
    $data['role'] = $this->_submitValues['role'];
    $data['cid'] = $this->_submitValues['cid'];
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }
}
