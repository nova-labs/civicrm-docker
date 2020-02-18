<?php

class CRM_Civirules_Delay_XWeekDayOfMonth extends CRM_Civirules_Delay_Delay {

  protected $week_offset;

  protected $day;

  protected $time_hour = '9';

  protected $time_minute = '00';

  public function delayTo(DateTime $date, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $d = clone $date;
    $d->modify('-30 minutes');
    $mod = $this->week_offset .' '.$this->day.' of this month';
    $date->modify($mod);
    $date->setTime((int) $this->time_hour, (int) $this->time_minute);
    if ($date <= $d) {
      $date->modify('first day of next month');
      $date->modify($mod);
    }

    return $date;
  }

  public function getDescription() {
    return ts('Nth weekday of month');
  }

  public function getDelayExplanation() {
    return ts('Delay to %1 %2 at %3:%4',
      array(
        1 => ts($this->week_offset),
        2 => ts($this->day),
        3 => $this->time_hour,
        4 => $this->time_minute < 10 && strlen($this->time_minute) <= 1 ? '0'.$this->time_minute : $this->time_minute,
      ));
  }

  public function addElements(CRM_Core_Form &$form, $prefix, CRM_Civirules_BAO_Rule $rule) {
    $form->add('select', $prefix.'XWeekDayOfMonth_week_offset', ts('Offset'), $this->getWeekOffset());
    $form->add('select', $prefix.'XWeekDayOfMonth_day', ts('Days'), $this->getDays());
    $form->add('text', $prefix.'XWeekDayOfMonth_time_hour', ts('Time (hour)'));
    $form->add('text', $prefix.'XWeekDayOfMonth_time_minute', ts('Time (minute)'));
  }

  protected function getDays() {
    return array(
      'sunday' => ts('Sunday'),
      'monday' => ts('Monday'),
      'tuesday' => ts('Tuesday'),
      'wednesday' => ts('Wednesday'),
      'thursday' => ts('Thursday'),
      'friday' => ts('Friday'),
      'saturday' => ts('Saturday'),
    );
  }

  protected function getWeekOffset() {
    return array(
      'first' => ts('First'),
      'second' => ts('Second'),
      'third' => ts('Third'),
      'fourth' => ts('Fourth'),
      'fifth' => ts('Fifth'),
      'last' => ts('Last'),
    );
  }

  public function validate($values, &$errors, $prefix, CRM_Civirules_BAO_Rule $rule) {
    if (empty($values[$prefix.'XWeekDayOfMonth_time_hour']) || !is_numeric($values[$prefix.'XWeekDayOfMonth_time_hour']) || $values[$prefix.'XWeekDayOfMonth_time_hour'] < 0 || $values[$prefix.'XWeekDayOfMonth_time_hour'] > 23) {
      $errors[$prefix.'XWeekDayOfMonth_time_hour'] = ts('You need to provide a number between 0 and 23');
    }
    if (empty($values[$prefix.'XWeekDayOfMonth_time_minute']) || !is_numeric($values[$prefix.'XWeekDayOfMonth_time_minute']) || $values[$prefix.'XWeekDayOfMonth_time_minute'] < 0 || $values[$prefix.'XWeekDayOfMonth_time_minute'] > 59) {
      $errors[$prefix.'XWeekDayOfMonth_time_minute'] = ts('You need to provide a number between 0 and 59');
    }
  }

  public function setValues($values,$prefix, CRM_Civirules_BAO_Rule $rule) {
    $this->week_offset = $values[$prefix.'XWeekDayOfMonth_week_offset'];
    $this->day = $values[$prefix.'XWeekDayOfMonth_day'];
    $this->time_hour = $values[$prefix.'XWeekDayOfMonth_time_hour'];
    $this->time_minute = $values[$prefix.'XWeekDayOfMonth_time_minute'];
  }

  public function getValues($prefix, CRM_Civirules_BAO_Rule $rule) {
    $values = array();
    $values[$prefix.'XWeekDayOfMonth_week_offset'] = $this->week_offset;
    $values[$prefix.'XWeekDayOfMonth_day'] = $this->day;
    $values[$prefix.'XWeekDayOfMonth_time_hour'] = $this->time_hour;
    $values[$prefix.'XWeekDayOfMonth_time_minute'] = $this->time_minute;
    return $values;
  }

  /**
   * Set default values
   *
   * @param $values
   */
  public function setDefaultValues(&$values, $prefix, CRM_Civirules_BAO_Rule $rule) {
    $values[$prefix.'XWeekDayOfMonth_time_hour'] = '9';
    $values[$prefix.'XWeekDayOfMonth_time_minute'] = '00';
  }

}