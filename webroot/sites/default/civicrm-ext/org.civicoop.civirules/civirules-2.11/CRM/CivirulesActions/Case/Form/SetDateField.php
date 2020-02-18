<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Case_Form_SetDateField extends CRM_CivirulesActions_Form_Form {

  protected function getFields() {
    return CRM_CivirulesActions_Case_SetDateFieldOnCase::getFields();
  }

  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');

    $this->add('select', 'field', ts('Field'), $this->getFields(), true, array('class' => 'crm-select2'));

    $delayList = array('' => ts(' - Set date to time of processing of action - ')) + CRM_Civirules_Delay_Factory::getOptionList();
    $this->add('select', 'date', ts('Set date'), $delayList);
    foreach(CRM_Civirules_Delay_Factory::getAllDelayClasses() as $delay_class) {
      $delay_class->addElements($this, 'date', $this->rule);
    }
    $this->assign('delayClasses', CRM_Civirules_Delay_Factory::getAllDelayClasses());

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
    if (!empty($data['field'])) {
      $defaultValues['field'] = $data['field'];
    }

    foreach(CRM_Civirules_Delay_Factory::getAllDelayClasses() as $delay_class) {
      $delay_class->setDefaultValues($defaultValues, 'date', $this->rule);
    }
    $activityDateClass = unserialize($data['date']);
    if ($activityDateClass) {
      $defaultValues['date'] = get_class($activityDateClass);
      foreach($activityDateClass->getValues('date', $this->rule) as $key => $val) {
        $defaultValues[$key] = $val;
      }
    }

    return $defaultValues;
  }

  /**
   * Function to add validation action rules (overrides parent function)
   *
   * @access public
   */
  public function addRules() {
    parent::addRules();
    $this->addFormRule(array(
      'CRM_CivirulesActions_Case_Form_SetDateField',
      'validateDate'
    ));
  }

  /**
   * Function to validate value of the delay
   *
   * @param array $fields
   * @return array|bool
   * @access public
   * @static
   */
  static function validateDate($fields) {
    $errors = array();
    if (!empty($fields['date'])) {
      $ruleActionId = CRM_Utils_Request::retrieve('rule_action_id', 'Integer');
      $ruleAction = new CRM_Civirules_BAO_RuleAction();
      $ruleAction->id = $ruleActionId;
      $ruleAction->find(true);
      $rule = new CRM_Civirules_BAO_Rule();
      $rule->id = $ruleAction->rule_id;
      $rule->find(true);

      $activityDateClass = CRM_Civirules_Delay_Factory::getDelayClassByName($fields['date']);
      $activityDateClass->validate($fields, $errors, 'date', $rule);
    }

    if (count($errors)) {
      return $errors;
    }

    return TRUE;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['date'] = false;
    if (!empty($this->_submitValues['date'])) {
      $scheduledDateClass = CRM_Civirules_Delay_Factory::getDelayClassByName($this->_submitValues['date']);
      $scheduledDateClass->setValues($this->_submitValues, 'date', $this->rule);
      $data['date'] = serialize($scheduledDateClass);
    }
    $data['field'] = $this->_submitValues['field'];

    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

}