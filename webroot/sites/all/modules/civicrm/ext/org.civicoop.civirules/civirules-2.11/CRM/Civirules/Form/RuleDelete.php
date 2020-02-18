<?php

use CRM_Civirules_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Civirules_Form_RuleDelete extends CRM_Core_Form {

  private $_ruleId = NULL;

  /**
   * Overridden parent method to build the form
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_id');
    $this->add('text', 'rule_label', 'Rule Label', array(), false);
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Confirm'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'),),));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    // check if rule is waiting on action in CiviCRM queue
    if (CRM_Civirules_BAO_Rule::isRuleOnQueue($this->_ruleId)) {
      $this->assign('rule_in_queue', TRUE);
    } else {
      $this->assign('rule_in_queue', FALSE);
    }


    parent::buildQuickForm();
  }

  /**
   * Overridden parent method to process form after submission
   */
  public function postProcess() {
    CRM_Civirules_BAO_Rule::deleteWithId($this->_submitValues['rule_id']);
    CRM_Core_Session::setStatus(ts('Rule successfully deleted from the database'), ts('Rule deleted'), 'success');
    parent::postProcess();
  }
  /**
   * Method to set the default values
   * @return array
   */
  public function setDefaultValues() {
    $defaults = array();
    if (isset($this->_ruleId) && !empty($this->_ruleId)) {
      $defaults['rule_id'] = $this->_ruleId;
      try {
        $ruleLabel = civicrm_api3('CiviRuleRule', 'getvalue', array(
          'id' => $this->_ruleId,
          'return' => 'label',
        ));
        $defaults['rule_label'] = (string) $ruleLabel;
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    return $defaults;
  }

  /**
   * Overridden parent method before form is processed
   */
  public function preProcess() {
    $requestValues = CRM_Utils_Request::exportValues();
    if (isset($requestValues['id'])) {
      $this->_ruleId = $requestValues['id'];
    }
    $this->context = CRM_Utils_System::url("civicrm/civirules/form/rulesview", "reset=1", TRUE);
    $this->controller->_destination = $this->context;
    CRM_Utils_System::setTitle(ts('Delete Rule (CiviRules)'));
  }



  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
