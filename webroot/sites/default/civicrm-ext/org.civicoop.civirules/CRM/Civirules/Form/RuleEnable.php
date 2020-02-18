<?php

use CRM_Civirules_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Civirules_Form_RuleEnable extends CRM_Core_Form {
  public function buildQuickForm() {
    $this->add('hidden', 'rule_id');
    $this->add('text', 'rule_label', 'Rule Label', array(), false);
    $this->addButtons(array(
      array('type' => 'next',   'name' => ts('Confirm'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'),),));
    parent::buildQuickForm();
  }

  public function preProcess(){
    $requestValues = CRM_Utils_Request::exportValues();
    if (isset($requestValues['id'])) {
      $this->ruleId = $requestValues['id'];
      $clones = civicrm_api3('CiviRuleRule', 'getclones', [
        'id' => $this->ruleId,
      ]);
      $cloneLabels = [];
      foreach($clones['values'] as $key => $clone){
        $cloneLabels[$key] = $clone['label'];
      }
      $this->assign('clones',implode(',',$cloneLabels));
    }
    $this->context = CRM_Utils_System::url("civicrm/civirules/form/rulesview", "reset=1", TRUE);
    $this->controller->_destination = $this->context;
    CRM_Utils_System::setTitle(ts('Enable Rule (CiviRules)'));
  }

  /**
   * Method to set the default values
   * @return array
   */
  public function setDefaultValues() {
    $defaults = array();
    if (isset($this->ruleId) && !empty($this->ruleId)) {
      $defaults['rule_id'] = $this->ruleId;
      try {
        $ruleLabel = civicrm_api3('CiviRuleRule', 'getvalue', array(
          'id' => $this->ruleId,
          'return' => 'label',
        ));
        $defaults['rule_label'] = (string) $ruleLabel;
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    return $defaults;
  }

  public function postProcess() {
    civicrm_api3('CiviRuleRule','create',[
      'id' => $this->_submitValues['rule_id'],
      'is_active' => 1
    ]);
    CRM_Core_Session::setStatus(ts('Rule successfully enabled'), ts('Rule enabled'), 'success');
    parent::postProcess();
  }

}
