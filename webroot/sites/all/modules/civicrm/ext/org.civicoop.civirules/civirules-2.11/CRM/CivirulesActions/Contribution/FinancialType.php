<?php
/**
 * Class CRM_CivirulesActions_Contribution_FinancialType
 *
 * CiviRules action: Sets the financial type of a Contribution
 *
 * @author Noah Miller (Lemniscus) <nm@lemnisc.us>
 * @license AGPL-3.0
 */
class CRM_CivirulesActions_Contribution_FinancialType extends CRM_Civirules_Action {
  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contribution = $triggerData->getEntityData('Contribution');
    $actionParams = $this->getActionParameters();
    $params = array(
      'id' => $contribution['id'],
      'financial_type_id' => $actionParams['financial_type_id']
    );
    try {
      civicrm_api3('Contribution', 'Create', $params);
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/contribution/financialtype', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns user friendly text explaining the condition params
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $actionParams = $this->getActionParameters();
    $financialTypeLabel = CRM_Core_Pseudoconstant::getLabel('CRM_Contribute_BAO_Contribution', 'financial_type_id', $actionParams['financial_type_id']);
    return 'Financial Type of Contribution will be set to "' . $financialTypeLabel . '"';
  }
}