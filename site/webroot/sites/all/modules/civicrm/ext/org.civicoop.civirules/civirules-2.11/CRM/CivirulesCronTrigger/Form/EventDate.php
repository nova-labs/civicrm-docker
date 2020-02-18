<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_CivirulesCronTrigger_Form_EventDate extends CRM_CivirulesTrigger_Form_Form {

  protected function getEventType() {
    return CRM_Civirules_Utils::getEventTypeList();
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_id');

    $this->add('select', 'event_type_id', ts('Event Type'), $this->getEventType(), true);
    $this->add('select', 'date_field', ts('Date Field'), array(
      'start_date' => ts('Start date'),
      'end_date' => ts('End date')
    ), true);
    $this->add('select', 'offset_unit', ts('Offset Unit'), array(
      'DAY' => ts('Day(s)'),
      'WEEK' => ts('Week(s)'),
      'MONTH' => ts('Month(s)'),
      'YEAR' => ts('Year(s)'),
    ), false);
    $this->add('select', 'offset_type', ts('Offset type'), array(
      '+' => ts('After'),
      '-' => ts('Before'),
    ), false);
    $this->add('text', 'offset', ts('Offset'), array(
      'class' => 'six',
    ), false);
    $this->add('checkbox', 'enable_offset', ts('Give a date offset'), '', false);

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
    if (!empty($data['event_type_id'])) {
      $defaultValues['event_type_id'] = $data['event_type_id'];
    }
    if (!empty($data['date_field'])) {
      $defaultValues['date_field'] = $data['date_field'];
    }
    if (!empty($data['offset_unit'])) {
      $defaultValues['offset_unit'] = $data['offset_unit'];
    }
    if (!empty($data['offset_type'])) {
      $defaultValues['offset_type'] = $data['offset_type'];
    }
    if (!empty($data['offset'])) {
      $defaultValues['offset'] = $data['offset'];
      $defaultValues['enable_offset'] = 1;
    }
    if (empty($date['offset'])) {
      $defaultValues['enable_offset'] = 0;
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
    $data['event_type_id'] = $this->_submitValues['event_type_id'];
    $data['date_field'] = $this->_submitValues['date_field'];
    $data['offset'] = '';
    if ($this->_submitValues['enable_offset']) {
      $data['offset_unit'] = $this->_submitValues['offset_unit'];
      $data['offset_type'] = $this->_submitValues['offset_type'];
      $data['offset'] = $this->_submitValues['offset'];
    } else {
      $data['offset_unit'] = $this->_submitValues['offset_unit'];
      $data['offset_type'] = $this->_submitValues['offset_type'];
      $data['offset'] = '';
    }

    $this->rule->trigger_params = serialize($data);
    $this->rule->save();

    parent::postProcess();
  }
}