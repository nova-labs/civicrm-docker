<?php
/**
 * Class for condition only once that operates on activity.
 * The goals is to make sure that the rule is not executed for each
 * activity contact, but only once for the activity
 *
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesConditions_Activity_OnlyOnce extends CRM_Civirules_Condition {

  public function getExtraDataInputUrl($ruleConditionId) {
    return FALSE;
  }

  /**
   * Method to check if the condition is valid. Goal of this condition is to make sure that
   * the action is only done once for an activity. This could be useful if the action is on
   * an activity contact level, for example sending an email. If you do not use this condition
   * the mail is sent for each activity contact, and this might be the same mail and the same
   * recipient. If you then use this condition too, it will only execute once.
   * This is achieved by abusing the fact that there is only 1 contact source, so
   * it will only execute if the activity contact record type id = 2 OR if there are no activity
   * contacts
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    try {
      $sourceRecordTypeId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group' => 'activity_contacts',
        'name' => 'Activity Source',
        'return' => 'value'
      ));
      $activityContactData = $triggerData->getEntityData('ActivityContact');
      if (empty($activityContactData) || $activityContactData['record_type_id'] == $sourceRecordTypeId) {
        $isConditionValid = TRUE;
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    return $isConditionValid;
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
    return $trigger->doesProvideEntity('ActivityContact');
  }
}