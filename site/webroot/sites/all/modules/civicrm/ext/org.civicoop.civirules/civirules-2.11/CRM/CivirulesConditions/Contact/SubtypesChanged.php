<?php
/**
 * Class for CiviRules SubtypesChanged
 *
 * @author Véronique Gratioulet <veronique.gratioulet@atd-quartmonde.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_SubtypesChanged extends CRM_CivirulesConditions_Generic_FieldValueChangeComparison {

  /**
   * Returns the value of the field for the condition
   * For example: I want to check if age > 50, this function would return the 50
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return mixed
   * @access protected
   */
  protected function getOriginalFieldValue(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $field = 'contact_sub_type';

    $data = $triggerData->getOriginalData();
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
   * @return mixed
   * @access protected
   */
  protected function getFieldValue(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $field = 'contact_sub_type';

    $data = $triggerData->getEntityData('Contact');
    if (isset($data[$field])) {
      return $data[$field];
    }

    return null;
  }

  /**
   * This function validates whether this condition works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether a condition is possible in the current setup. E.g. we could have a condition
   * which works on contribution or on contributionRecur then this function could do
   * this kind of validation and return false/true
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    if ($trigger instanceof CRM_Civirules_TriggerData_Interface_OriginalData) {
      return $trigger->doesProvideEntity('Activity');
    }
    return false;
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
    return CRM_Contact_BAO_ContactType::subTypePairs(null, true, null);
  }

  /**
   * Returns true when the field is a select option with multiple select
   *
   * @see getFieldOptions
   * @return bool
   */
  public function isMultiple() {
    return true;
  }

}