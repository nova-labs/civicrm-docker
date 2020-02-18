<?php
/**
 * Class for CiviRules Contribution status changed
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contribution_StatusChanged extends CRM_CivirulesConditions_Generic_FieldValueChangeComparison {
	
	/**
   * Returns the value of the field for the condition
   * For example: I want to check if age > 50, this function would return the 50
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return
   * @access protected
   * @abstract
   */
  protected function getOriginalFieldValue(CRM_Civirules_TriggerData_TriggerData $triggerData) {
  	$entity = $this->getEntity();
    if ($triggerData->getOriginalEntity() != $entity) {
      return null;
    }

    $data = $triggerData->getOriginalData();
    $field = $this->getField();
    if (isset($data[$field])) {
      return $data[$field];
    }
    return null;
  }
	
	/**
   * Returns the value of the field for the condition
   * For example: I want to check if age > 50, this function would return the 50
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return
   * @access protected
   * @abstract
   */
  protected function getFieldValue(CRM_Civirules_TriggerData_TriggerData $triggerData) {
  	$entity = $this->getEntity();
    $data = $triggerData->getEntityData($entity);
    $field = $this->getField();
    if (isset($data[$field])) {
      return $data[$field];
    }
    return null;
  }	
	
	/**
   * Returns an array with all possible options for the field, in
   * case the field is a select field, e.g. gender, or financial type
   * Return false when the field is a select field
   *
   * This method could be overriden by child classes to return the option
   *
   * The return is an array with the field option value as key and the option label as value
   *
   * @return bool
   */
  public function getFieldOptions() {
    return CRM_Core_BAO_OptionValue::getOptionValuesAssocArrayFromName('contribution_status');
  }

  /**
   * Returns name of entity
   *
   * @return string
   * @access protected
   */
  protected function getEntity() {
    return 'Contribution';
  }

  /**
   * Returns name of the field
   *
   * @return string
   * @access protected
   */
  protected function getField() {
    return 'contribution_status_id';
  }

}