<?php
/**
 * Class for CiviRule Condition Activity Date is .....
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 May 2018
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesConditions_Activity_Date extends CRM_Civirules_Condition {

  private $_conditionParams = array();

  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/activity/date',
      'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->_conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->_conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method to check if the condition is valid, will check if the contact
   * has an activity of the selected type
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $activityData = $triggerData->getEntityData('Activity');
    if (isset($activityData['activity_date_time'])) {
      try {
        $activityDate = new DateTime($activityData['activity_date_time']);
      }
      catch (Exception $ex) {
        Civi::log()->error(ts('Could not parse activity_date_time ') . $activityData['activity_date_time']
          . ts(' into a DateTime object in ') . __METHOD__ . ts(', condition returned as FALSE'));
        return FALSE;
      }
      if ($activityDate) {
        if ($this->_conditionParams['operator'] == 6) {
          try {
            $fromDate = new DateTime($this->_conditionParams['activity_from_date']);
            $toDate = new DateTime($this->_conditionParams['activity_to_date']);
          }
          catch (Exception $ex) {
            Civi::log()->error(ts('Could not parse either from date or to date from the condition params into a DateTime object in ') . __METHOD__ . ts(', condition returned as FALSE'));
            return FALSE;
          }
          if ($fromDate >= $activityDate && $toDate <= $activityDate) {
            return TRUE;
          }
        }
        else {
          $compareDate = $this->getCompareDate($triggerData);
          if ($compareDate) {
            return $this->compareDate($compareDate, $activityDate);
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to compare activity date and compare date
   *
   * @param DateTime $compareDate
   * @param DateTime $activityDate
   * @return bool
   */
  private function compareDate($compareDate, $activityDate) {
    switch ($this->_conditionParams['operator']) {
      case 0:
        if ($activityDate == $compareDate) {
          return TRUE;
        }
        break;
      case 1:
        if ($activityDate > $compareDate) {
          return TRUE;
        }
        break;
      case 2:
        if ($activityDate >= $compareDate) {
          return TRUE;
        }
        break;
      case 3:
        if ($activityDate < $compareDate) {
          return TRUE;
        }
        break;
      case 4:
        if ($activityDate <= $compareDate) {
          return TRUE;
        }
        break;
      case 5:
        if ($activityDate != $compareDate) {
          return TRUE;
        }
        break;
    }
    return FALSE;
  }

  /**
   * Method to get the compare date
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool|DateTime
   */
  private function getCompareDate($triggerData) {
    // if use_trigger_date, compare with trigger date
    if ($this->_conditionParams['use_trigger_date'] && !$this->_conditionParams['use_action_date'] && empty($this->_conditionParams['activity_compare_date'])) {
      $dateToUse = date('YmdHis');
      if ($triggerData->isDelayedExecution) {
        if (isset($triggerData->delayedSubmitDateTime)) {
          $dateToUse = $triggerData->delayedSubmitDateTime;
        }
      }
      // if use_action_date, use date (only makes sense for actions with delays)
    } elseif ($this->_conditionParams['use_action_date'] && !$this->_conditionParams['use_trigger_date'] && empty($this->_conditionParams['activity_compare_date'])) {
      $dateToUse = date('YmdHis');
    }
    else {
      // if compare date has been used
      if (!empty($this->_conditionParams['activity_compare_date']) && !$this->_conditionParams['use_trigger_date'] && !$this->_conditionParams['use_action_date']) {
        $dateToUse = $this->_conditionParams['activity_compare_date'];
      }
    }
    if ($dateToUse) {
      try {
        // weird fix for the fact that activity_date_time does not hold seconds.....
        $dateToUse = substr($dateToUse, 0, -2) . '00';
        $compareDate = new DateTime($dateToUse);
        return $compareDate;
      }
      catch (Exception $ex) {
        Civi::log()->error(ts('Could not parse date ') . $this->_conditionParams['activity_compare_date'] . ' in ' . __METHOD__);
      }
    }
    return FALSE;
  }

  /**
   * Returns a user friendly text explaining the condition params
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $operatorOptions = CRM_Civirules_Utils::getActivityDateOperatorOptions();
    $friendlyText = ts("Activity Date ") . ts($operatorOptions[$this->_conditionParams['operator']]);
    if ($this->_conditionParams['operator'] == 6) {
      try {
        $fromDate = new DateTime($this->_conditionParams['activity_from_date']);
        $toDate = new DateTime($this->_conditionParams['activity_to_date']);
        $friendlyText .= ' ' . $fromDate->format('j F Y') . ts(' and ') . $toDate->format('j F Y');
      }
      catch (Exception $ex) {
      }
    }
    else {
      // if use_trigger_date
      if ($this->_conditionParams['use_trigger_date'] == 1) {
        $friendlyText .= ' the date the rule is triggered.';
      }
      elseif ($this->_conditionParams['use_action_date'] == 1) {
        // if use_action_date
        $friendlyText .= ' the date the action is executed.';
      }
      else {
        try {
          $compareDate = new DateTime($this->_conditionParams['activity_compare_date']);
          $friendlyText .= ' ' . $compareDate->format('j F Y');
        }
        catch (Exception $ex) {
          $friendlyText = 'Could not parse dates!';
        }
      }
    }
    return $friendlyText;
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
