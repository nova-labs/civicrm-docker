<?php

class CRM_Civirules_Delay_XDays extends CRM_Civirules_Delay_Delay {

  protected $dayOffset;

  public function delayTo(DateTime $date, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $date->modify("+ ".$this->dayOffset." days");
    return $date;
  }

  public function getDescription() {
    return ts('Delay by a number of days');
  }

  public function getDelayExplanation() {
    return ts('Delay by %1 days', array(1 => $this->dayOffset));
  }

  public function addElements(CRM_Core_Form &$form, $prefix, CRM_Civirules_BAO_Rule $rule) {
    $form->add('text', $prefix.'xdays_dayOffset', ts('Days'));
  }

  public function validate($values, &$errors, $prefix, CRM_Civirules_BAO_Rule $rule) {
    if (empty($values[$prefix.'xdays_dayOffset']) || !is_numeric($values[$prefix.'xdays_dayOffset'])) {
      $errors[$prefix.'xdays_dayOffset'] = ts('You need to provide a number of days');
    }
  }

  public function setValues($values, $prefix, CRM_Civirules_BAO_Rule $rule) {
    $this->dayOffset = $values[$prefix.'xdays_dayOffset'];
  }

  public function getValues($prefix, CRM_Civirules_BAO_Rule $rule) {
    $values = array();
    $values[$prefix.'xdays_dayOffset'] = $this->dayOffset;
    return $values;
  }

}