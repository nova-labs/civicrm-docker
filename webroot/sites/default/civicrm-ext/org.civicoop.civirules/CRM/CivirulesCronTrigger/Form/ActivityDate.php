<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_CivirulesCronTrigger_Form_ActivityDate extends CRM_CivirulesTrigger_Form_Form {

  protected function getActivityType() {
    return CRM_Civirules_Utils::getActivityTypeList();
  }

  protected function getActivityStatus() {
    $activityStatusList = array();
    $activityStatusOptionGroupId = CRM_Civirules_Utils::getOptionGroupIdWithName('activity_status');
    $params = array(
      'option_group_id' => $activityStatusOptionGroupId,
      'is_active' => 1,
      'options' => array('limit' => 0));
    $activityStatuses = civicrm_api3('OptionValue', 'Get', $params);
    foreach ($activityStatuses['values'] as $optionValue) {
      $activityStatusList[$optionValue['value']] = $optionValue['label'];
    }
    return $activityStatusList;
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_id');

    $this->add('select', 'activity_type_id', ts('Activity Type'), $this->getActivityType(), true);
    $this->add('select', 'activity_status_id', ts('Activity Status'), $this->getActivityStatus(), true);

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
    if (!empty($data['activity_type_id'])) {
      $defaultValues['activity_type_id'] = $data['activity_type_id'];
    }
    if (!empty($data['activity_status_id'])) {
      $defaultValues['activity_status_id'] = $data['activity_status_id'];
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
    $data['activity_type_id'] = $this->_submitValues['activity_type_id'];
    $data['activity_status_id'] = $this->_submitValues['activity_status_id'];
    $this->rule->trigger_params = serialize($data);
    $this->rule->save();

    parent::postProcess();
  }
}