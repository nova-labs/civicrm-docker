<?php
/**
 * Class for CiviRules Set Thank You Date for Contribution Action
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_CivirulesActions_Contribution_ThankYouDate extends CRM_Civirules_Action {
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
    switch ($actionParams['thank_you_date_radio']) {
      case 1:
        if (!empty($actionParams['number_of_days'])) {
          $thankYouDate = new DateTime();
          $thankYouDate->modify('+'.$actionParams['number_of_days']. ' day');
          }
        break;
      case 2:
        $thankYouDate = new DateTime($actionParams['thank_you_date']);
        break;
      default:
        $thankYouDate = new DateTime();
        break;
    }
    if ($actionParams['thank_you_time']) {
      list($hours, $minutes, $seconds) = explode(':', $actionParams['thank_you_time']);
      $thankYouDate->setTime($hours, $minutes, $seconds);
    }
    // Handle the legacy case (set time to midnight).
    if (empty($actionParams['thank_you_time']) && empty($actionParams['thank_you_time_radio'])) {
      $thankYouDate->setTime(0, 0, 0);
    }
    $params = array(
      'id' => $contribution['id'],
      'thankyou_date' => $thankYouDate->format('YmdHis')
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
    return CRM_Utils_System::url('civicrm/civirule/form/action/contribution/thankyoudate', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $return = "";
    $dateString = "";
    $params = $this->getActionParameters();
    // Handle the legacy field name.
    if (isset($params['thank_you_radio'])) {
      $params['thank_you_date_radio'] = $params['thank_you_radio'];
    }

    if (isset($params['thank_you_date_radio'])) {
      switch ($params['thank_you_date_radio']) {
        case 0:
          $dateString = "date action executes";
          break;
        case 1:
          $dateString = $params['number_of_days']." days after action executes";
          break;
        case 2:
          $dateString = date('d M Y', strtotime($params['thank_you_date']));
          break;
      }
    }
    if (!empty($dateString)) {
      $return = 'Thank You Date for Contribution will be set to : ' . $dateString;
    }
    if (isset($params['thank_you_time'])) {
      $return .= ' at ' . $params['thank_you_time'];
    }
    // Handle the legact case.
    if (!isset($params['thank_you_time_radio'])) {
      $return .= ' at 12:00:00 AM';
    }
    return $return;
  }
}
