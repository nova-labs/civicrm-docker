<?php
/**
 * Class to process action set privacy options for contact
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 29 Oct 2017
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Contact_SetPrivacyOptions extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @throws Exception when error from API Contact create
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    try {
      $sqlUpd = [];
      $v = CRM_Utils_Type::escape($actionParams['on_or_off'], 'Integer');

      foreach ($actionParams['privacy_options'] as $privacyOption) {
        $f = 'do_not_'.$privacyOption;
        $sqlUpd[] = "{$f} = {$v}";
      }
      $sqlUpdStr = implode(', ', $sqlUpd);

      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_contact
        SET {$sqlUpdStr}
        WHERE id = %1
      ", array(
        1 => array($triggerData->getContactId(), 'Positive')
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not update contact with privacy options in '.__METHOD__
        .', contact your system administrator. Error from API Contact create: '.$ex->getMessage());
    }
  }

  /**
   * Method to add url for form action for rule
   *
   * @param int $ruleActionId
   * @return string
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/contact/privacyoptions', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Method to create a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $privacyOptions = array(
      'phone' => 'Do not phone',
      'email' => 'Do not email',
      'mail' => 'Do not mail',
      'sms' => 'Do not SMS',
      'trade' => 'Do not trade',
    );
    $actionLabels = array();
    $actionParams = $this->getActionParameters();
    foreach ($actionParams['privacy_options'] as $actionParam) {
      $actionLabels[] = ts($privacyOptions[$actionParam]);
    }
    $label = ts('Privacy option(s) ').implode(', ', $actionLabels).' '.ts('switched').' ';
    if ($actionParams['on_or_off'] == 1) {
      $label .= ts('ON');
    } else {
      $label .= 'OFF';
    }
    return $label;
  }

}
