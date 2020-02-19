<?php
/**
 * Class for CiviRules ActivityIsPast Condition
 *
 * @author A.J. Fasano (Grand Lodge of Virginia AF&AM)
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Activity_ActivityIsPast extends CRM_Civirules_Condition {

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return FALSE;
  }

  /**
   * Method is mandatory and checks if the condition is met
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData)
  {
    $activityData = $triggerData->getEntityData('Activity');
    $activityDate = new DateTime($activityData['activity_date_time']);
    $currentDate = new DateTime();

    if ($activityDate < $currentDate)
    {
      return TRUE;
    }
    return FALSE;
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
    return $trigger->doesProvideEntity('Activity');
  }
}