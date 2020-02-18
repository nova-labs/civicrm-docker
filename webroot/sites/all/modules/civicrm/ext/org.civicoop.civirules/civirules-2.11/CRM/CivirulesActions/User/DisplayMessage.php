<?php

/**
 * Class for CiviRules Display Message Action
 *
 * @author John Kirk (CiviFirst) <john@civifirst.com>
 * @license AGPL-3.0
 */
class CRM_CivirulesActions_User_DisplayMessage extends CRM_Civirules_Action {
  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_params = $this->getActionParameters();

    CRM_Core_Session::setStatus($action_params['message'], ts($action_params['title']), $action_params['type']);
  }

  /**
   * Method to return the url for additional form processing for action
   * and return false if none is needed
   *
   * @param int $ruleActionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/display_message/', 'rule_action_id='.$ruleActionId);
  }
}