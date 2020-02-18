<?php
/**
 * Created by PhpStorm.
 * User: Klaas
 * Date: 28-4-2017
 * Time: 15:11
 */
class CRM_CivirulesActions_TestAction extends  CRM_Civirules_Action{

  private static $_fired = FALSE;
   /**
   * Method to return the url for additional form processing for action
   * and return false if none is needed (which is the case in this test)
   *
   * @param int $ruleActionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return FALSE;
  }

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    CRM_CivirulesActions_TestAction::$_fired= TRUE;
    if($this->getActionParameters()['is_enabled']) {
      CRM_Core_Error::debug_var('triggerData', $triggerData);
    }

    if($this->getActionParameters()['print_r_enabled']) {
      print_r($triggerData);
    }

    CRM_Core_Error::debug_var('actionParameters',$this->getActionParameters());
  }

  public static function report(){
     if(CRM_CivirulesActions_TestAction::$_fired){
        return 'The action is executed';
     } else {
        return 'The action is NOT executed';
     }
  }

}