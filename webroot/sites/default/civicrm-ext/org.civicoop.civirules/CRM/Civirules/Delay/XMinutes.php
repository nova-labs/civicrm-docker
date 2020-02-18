<?php

class CRM_Civirules_Delay_XMinutes extends CRM_Civirules_Delay_Delay {

  protected $minuteOffset;

  public function delayTo(DateTime $date, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $date->modify("+ ".$this->minuteOffset." minutes");
    return $date;
  }

  public function getDescription() {
    return ts('Delay by a number of minutes');
  }

  public function getDelayExplanation() {
    return ts('Delay by %1 minutes', array(1 => $this->minuteOffset));
  }

  public function addElements(CRM_Core_Form &$form, $prefix, CRM_Civirules_BAO_Rule $rule) {
    $form->add('text', $prefix.'xminutes_minuteOffset', ts('Minutes'));
  }

  public function validate($values, &$errors,$prefix, CRM_Civirules_BAO_Rule $rule) {
    if (empty($values[$prefix.'xminutes_minuteOffset']) || !is_numeric($values[$prefix.'xminutes_minuteOffset'])) {
      $errors[$prefix.'xminutes_minuteOffset'] = ts('You need to provide a number of minutess');
    }
  }

  public function setValues($values,$prefix, CRM_Civirules_BAO_Rule $rule) {
    $this->minuteOffset = $values[$prefix.'xminutes_minuteOffset'];
  }

  public function getValues($prefix, CRM_Civirules_BAO_Rule $rule) {
    $values = array();
    $values[$prefix.'xminutes_minuteOffset'] = $this->minuteOffset;
    return $values;
  }

}