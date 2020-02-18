<?php
/**
 * Class to process action set communication preferences for contact
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 10 Nov 2017
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Contact_SetCommPref extends CRM_Civirules_Action {

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
    $contactId = $triggerData->getContactId();
    if (!$contactId) {
      $entityTag = $triggerData->getEntityData('EntityTag');
      // todo if tag used contains civicrm_contact and cater for many entity ids
      $params['id'] = $entityTag['entity_id'];
    } else {
      $params['id'] = $contactId;
    }
    $params['preferred_communication_method'] = array();
    try {
      //retrieve current settings for contact
      $currentCommPrefs = civicrm_api3('Contact', 'getvalue', array(
        'id' => $params['id'],
        'return' => 'preferred_communication_method'
      ));
      if (!empty($currentCommPrefs) && !is_array($currentCommPrefs)) {
        $currentCommPrefs = [$currentCommPrefs];
      }
      if ($actionParams['on_or_off'] == 0 && isset($actionParams['comm_pref'])) {
        foreach ($currentCommPrefs as $currentKey => $currentValue) {
          if (!in_array($currentValue, $actionParams['comm_pref'])) {
            $params['preferred_communication_method'][] = $currentValue;
          }
        }
      } else {
        if (!empty($currentCommPrefs)) {
          $params['preferred_communication_method'] = $currentCommPrefs;
        }
        if (!empty($actionParams['comm_pref'])) {
          foreach ($actionParams['comm_pref'] as $newKey => $newValue) {
            if (empty($params['preferred_communication_method']) || !in_array($newValue, $params['preferred_communication_method'])) {
              $params['preferred_communication_method'][] = $newValue;
            }
          }
        }
      }
      civicrm_api3('Contact', 'create', $params);
    }
    catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not update contact with communication preferences in '.__METHOD__
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
    return CRM_Utils_System::url('civicrm/civirule/form/action/contact/commpref', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Method to create a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $commPrefs = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => 'preferred_communication_method',
      'is_active' => 1,
      'options' => array('limit' => 0)
    ));
    $actionLabels = array();
    $actionParams = $this->getActionParameters();
    if (isset($actionParams['comm_pref'])) {
      foreach ($actionParams['comm_pref'] as $key => $actionParam) {
        foreach ($commPrefs['values'] as $commPref) {
          if ($commPref['value'] == $actionParam) {
            $actionLabels[] = $commPref['label'];
          }
        }
      }
    }
    $label = ts('Communication Preference(s) ').implode(', ', $actionLabels).' '.ts('switched').' ';
    if ($actionParams['on_or_off'] == 1) {
      $label .= ts('ON');
    } else {
      $label .= 'OFF';
    }
    return $label;
  }

}