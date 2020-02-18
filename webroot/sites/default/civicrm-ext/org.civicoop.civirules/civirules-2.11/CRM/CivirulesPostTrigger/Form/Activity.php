<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesPostTrigger_Form_Activity extends CRM_CivirulesTrigger_Form_Form {

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
    $result = civicrm_api3('ActivityContact', 'getoptions', [
      'field' => "record_type_id",
    ]);
    $options[0] = E::ts('For all contacts');
    $options = array_merge($options, $result['values']);

    $this->add('select', 'record_type', E::ts('Trigger for'),$options, true, ['class' => 'crm-select2 huge']);

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
    if ($data === false && $this->ruleId) {
      $defaultValues['record_type'] = 0; // Default to all record types. This creates backwards compatibility.
    } elseif (!empty($data['record_type'])) {
      $defaultValues['record_type'] = $data['record_type'];
    } else {
      $defaultValues['record_type'] = 3; // Default to only targets
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
    $data['record_type'] = $this->_submitValues['record_type'];
    $this->rule->trigger_params = serialize($data);
    $this->rule->save();

    parent::postProcess();
  }
}
