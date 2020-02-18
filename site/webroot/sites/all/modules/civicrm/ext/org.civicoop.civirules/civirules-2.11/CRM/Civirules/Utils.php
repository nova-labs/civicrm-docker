<?php
/**
 * Utils - class with generic functions CiviRules
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Civirules_Utils {

  /**
   * Function return display name of contact retrieved with contact_id
   *
   * @param int $contactId
   * @return string $contactName
   * @access public
   * @static
   */
  public static function getContactName($contactId) {
    if (empty($contactId)) {
      return '';
    }
    $params = array(
      'id' => $contactId,
      'return' => 'display_name');
    try {
      $contactName = civicrm_api3('Contact', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      $contactName = '';
    }
    return $contactName;
  }

  /**
   * Function to format is_active to yes/no
   *
   * @param int $isActive
   * @return string
   * @access public
   * @static
   */
  public static function formatIsActive($isActive) {
    if ($isActive == 1) {
      return ts('Yes');
    } else {
      return ts('No');
    }
  }

  /**
   * Public function to generate name from label
   *
   * @param $label
   * @return string
   * @access public
   * @static
   */
  public static function buildNameFromLabel($label) {
    $labelParts = explode(' ', strtolower($label));
    $nameString = implode('_', $labelParts);
    return substr($nameString, 0, 80);
  }

  /**
   * Public function to generate label from name
   *
   * @param $name
   * @return string
   * @access public
   * @static
   */
  public static function buildLabelFromName($name) {
    $labelParts = array();
    $nameParts = explode('_', strtolower($name));
    foreach ($nameParts as $namePart) {
      $labelParts[] = ucfirst($namePart);
    }
    return implode(' ', $labelParts);
  }

  /**
   * Function to build the trigger list
   *
   * @return array $triggerList
   * @access public
   * @static
   */
  public static function buildTriggerList() {
    $triggerList = array();
    $triggers = CRM_Civirules_BAO_Trigger::getValues(array());
    foreach ($triggers as $triggerId => $trigger) {
      $triggerList[$triggerId] = $trigger['label'];
    }
    return $triggerList;
  }

  /**
   * Function to build the conditions list
   *
   * @return array $conditionList
   * @access public
   * @static
   */
  public static function buildConditionList() {
    $conditionList = array();
    $conditions = CRM_Civirules_BAO_Condition::getValues(array());
    foreach ($conditions as $conditionId => $condition) {
      $conditionList[$conditionId] = $condition['label'];
    }
    return $conditionList;
  }

  /**
   * Function to build the action list
   *
   * @return array $actionList
   * @access public
   * @static
   */
  public static function buildActionList() {
    $actionList = array();
    $actions = CRM_Civirules_BAO_Action::getValues(array());
    foreach ($actions as $actionId => $action) {
      $actionList[$actionId] = $action['label'];
    }
    return $actionList;
  }

  /**
   * Function to return activity status list
   *
   * @return array $activityStatusList
   * @access public
   */
  public static function getActivityStatusList() {
    $activityStatusList = array();
    $activityStatusOptionGroupId = self::getOptionGroupIdWithName('activity_status');
    $params = array(
      'option_group_id' => $activityStatusOptionGroupId,
      'is_active' => 1,
      'options' => array('limit' => 0));
    $activityStatuses = civicrm_api3('OptionValue', 'Get', $params);
    foreach ($activityStatuses['values'] as $optionValue) {
      $activityStatusList[$optionValue['value']] = $optionValue['label'];
    }
    return $activityStatusList;
  }

  /**
   * Function to return activity type list
   *
   * @return array $activityTypeList
   * @access public
   */
  public static function getActivityTypeList() {
    $activityTypeList = array();
    $activityTypeOptionGroupId = self::getOptionGroupIdWithName('activity_type');
    $params = array(
      'option_group_id' => $activityTypeOptionGroupId,
      'is_active' => 1,
      'options' => array('limit' => 0));
    $activityTypes = civicrm_api3('OptionValue', 'Get', $params);
    foreach ($activityTypes['values'] as $optionValue) {
      $activityTypeList[$optionValue['value']] = $optionValue['label'];
    }
    return $activityTypeList;
  }

  /**
   * Function to return campaign type list
   *
   * @return array $campaignTypeList
   * @access public
   */
  public static function getCampaignTypeList() {
    $campaignTypeList = [];
    $campaignTypeOptionGroupId = self::getOptionGroupIdWithName('campaign_type');
    $params = [
      'option_group_id' => $campaignTypeOptionGroupId,
      'is_active' => 1,
      'options' => ['limit' => 0],
      ];
    $campaignTypes = civicrm_api3('OptionValue', 'get', $params);
    foreach ($campaignTypes['values'] as $optionValue) {
      $campaignTypeList[$optionValue['value']] = $optionValue['label'];
    }
    return $campaignTypeList;
  }

  /**
   * Function to get the option group id of an option group with name
   *
   * @param string $optionGroupName
   * @return int $optionGroupId
   * @throws Exception when no option group activity_type is found
   */
  public static function getOptionGroupIdWithName($optionGroupName) {
    $params = array(
      'name' => $optionGroupName,
      'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with the name '.$optionGroupName.
        ', error from API OptionGroup Getvalue: '.$ex->getMessage());
    }
    return $optionGroupId;
  }

  /**
   * Function to get option label with value and option group id
   *
   * @param int $optionGroupId
   * @param mixed $optionValue
   * @return array|bool
   * @access public
   * @static
   */
  public static function getOptionLabelWithValue($optionGroupId, $optionValue) {
    if (empty($optionGroupId) or empty($optionValue)) {
      return FALSE;
    } else {
      $params = array(
        'option_group_id' => $optionGroupId,
        'value' => $optionValue,
        'return' => 'label'
      );
      try {
        return civicrm_api3('OptionValue', 'Getvalue', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        return false;
      }
    }
  }

  /**
   * Method to get the contribution status id with name
   *
   * @param string $statusName
   * @return int $statusId
   * @access public
   * @throws Exception when error from API
   * @static
   */
  public static function getContributionStatusIdWithName($statusName) {
    $optionGroupId = self::getOptionGroupIdWithName('contribution_status');
    $optionValueParams = array(
      'option_group_id' => $optionGroupId,
      'name' => $statusName,
      'return' => 'value');
    try {
      $statusId = (int) civicrm_api3('OptionValue', 'Getvalue', $optionValueParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not retrieve a contribution status with name '.
        $statusName.', contact your system administrator. Error from API OptionValue Getvalue: '.$ex->getMessage());
    }
    return $statusId;
  }

  /**
   * Method to get the financial types
   * @return array
   */
  public static function getFinancialTypes() {
    $return = array();
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_financial_type` where `is_active` = 1");
    while($dao->fetch()) {
      $return[$dao->id] = $dao->name;
    }
    return $return;
  }

  /**
   * Method to get the membership types
   * @param bool $onlyActive
   * @return array
   */
  public static function getMembershipTypes($onlyActive = TRUE) {
    $return = array();
    if ($onlyActive) {
      $params = array('is_active' => 1);
    } else {
      $params = array();
    }
    $params['options'] = array('limit' => 0, 'sort' => "name ASC");
    try {
      $membershipTypes = civicrm_api3("MembershipType", "Get", $params);
      foreach ($membershipTypes['values'] as $membershipType) {
        $return[$membershipType['id']] = $membershipType['name'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    return $return;
  }

  /**
   * Method to get the membership status
   * @param bool $onlyActive
   * @return array
   */
  public static function getMembershipStatus($onlyActive = TRUE) {
    $return = array();
    if ($onlyActive) {
      $params = array('is_active' => 1);
    } else {
      $params = array();
    }
    try {
      $apiMembershipStatus = civicrm_api3("MembershipStatus", "Get", $params);
      foreach ($apiMembershipStatus['values'] as $membershipStatus) {
        $return[$membershipStatus['id']] = $membershipStatus['name'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    return $return;
  }

  /**
   * Method to check if the incoming date is later than today
   *
   * @param mixed $inDate
   * @return boolean
   * @access public
   * @static
   */
  public static function endDateLaterThanToday($inDate) {
    $isLater = FALSE;
    try {
      $dateToBeChecked = new DateTime($inDate);
      $now = new DateTime();
      if ($dateToBeChecked > $now) {
        $isLater = TRUE;
      }
    } catch (Exception $ex) {}
    return $isLater;
  }

  /**
   * Method to calculate maximum menu key for navigationMenu hook
   *
   * @param $menuArray
   * @return mixed
   */
  public static function getMenuKeyMax($menuArray) {
    $max = array(max(array_keys($menuArray)));
    foreach($menuArray as $v) {
      if (!empty($v['child'])) {
        $max[] = self::getMenuKeyMax($v['child']);
      }
    }
    return max($max);
  }

  /**
   * Method to get the activity type list
   *
   * @return array
   */
  public static function getCampaignList() {
    $campaignList = array();
    try {
      $campaigns = civicrm_api3('Campaign', 'get', array(
        'sequential' => 1,
        'is_active' => 1,
        'options' => array('limit' => 0),
      ));
      foreach ($campaigns['values'] as $campaign) {
        if (isset($campaign['title'])) {
          $campaignList[$campaign['id']] = $campaign['title'];
        }
        else {
          $campaignList[$campaign['id']] = ts('(no title)');
        }
      }
      asort($campaignList);
    }
    catch (CiviCRM_API3_Exception $ex) {
      $campaignList = array();
    }
    return $campaignList;
  }

  /**
   * Function to return event type list
   *
   * @return array $eventTypeList
   * @access public
   */
  public static function getEventTypeList() {
    $eventTypeList = array();
    $eventTypeOptionGroupId = self::getOptionGroupIdWithName('event_type');
    $params = array(
      'option_group_id' => $eventTypeOptionGroupId,
      'is_active' => 1,
      'options' => array('limit' => 0));
    $eventTypes = civicrm_api3('OptionValue', 'Get', $params);
    foreach ($eventTypes['values'] as $optionValue) {
      $eventTypeList[$optionValue['value']] = $optionValue['label'];
    }
    return $eventTypeList;
  }

  /**
   * Method to set the date operator options
   *
   * @return array
   */
  public static function getActivityDateOperatorOptions() {
    return array(
      'equals',
      'later than',
      'later than or equal',
      'earlier than',
      'earlier than or equal',
      'not equal',
      'between',
    );
  }

  /**
   * Method to set the generic comparison operators
   *
   * @return array
   */
  public static function getGenericComparisonOperatorOptions() {
    return array(
      'equals',
      'greater than',
      'greater than or equal',
      'less than',
      'less than or equal',
      'not equal',
    );
  }

  /**
   * Method to get the CiviCRM version
   *
   * @return float
   * @throws CiviCRM_API3_Exception
   */
  public static function getCiviVersion() {
    $apiVersion = (string) civicrm_api3('Domain', 'getvalue', array('current_domain' => "TRUE", 'return' => 'version'));
    $civiVersion = (float) substr($apiVersion, 0, 3);
    return $civiVersion;
  }

  /**
   * Method to get the civirules base path
   *
   * @return string
   * @throws CiviCRM_API3_Exception
   */
  public static function getCivirulesPath() {
    $version = CRM_Core_BAO_Domain::version();
    if ($version >= 4.7) {
      $container = CRM_Extension_System::singleton()->getFullContainer();
      return $container->getPath('org.civicoop.civirules');
    }
    else {
      $settings = civicrm_api3('Setting', 'getsingle', []);
      $path = $settings['extensionsDir'].'/civirules/';
      if (is_dir($path)) {
        return $path;
      }
      else {
        return $settings['extensionsDir'].'/org.civicoop/civirules/';
      }
    }
  }


  /**
   * Reads a part of the rule into an array to make it comparable with
   * other rules. Used to determine of both rules are clones of each other,
   * rules with the same actions
   *
   * @param $ruleId
   *
   * @return array
   */
  public static function ruleCompareFormat($ruleId, $triggerId = NULL) {

    $result = [];
    if (!$triggerId) {
      $triggerId = civicrm_api3('CiviRuleRule', 'getvalue', [
        'id' => $ruleId,
        'return' => 'trigger_id'
      ]);
    }
    $result['triggerId'] = $triggerId;

    $dao = CRM_Core_DAO::executeQuery('SELECT condition_link,condition_id,condition_params,is_active FROM civirule_rule_condition WHERE rule_id = %1 ORDER BY id', [
      1 => [$ruleId, 'Integer']
    ]);

    $result ['conditions'] = [];
    while ($dao->fetch()) {
      $result ['conditions'][] = [
        'condition_link' => $dao->condition_link,
        'condition_id' => $dao->condition_id,
        'condition_params' => $dao->condition_params,
        'is_active' => $dao->is_active,
      ];
    };

    $dao = CRM_Core_DAO::executeQuery('SELECT action_id ,action_params, delay, ignore_condition_with_delay, is_active FROM civirule_rule_action WHERE rule_id = %1 ORDER BY id', [
      1 => [$ruleId, 'Integer']
    ]);
    $result['actions'] = [];
    while ($dao->fetch()){
      $result ['actions'][] = [
        'action_id' => $dao->action_id,
        'action_params' => $dao->action_params,
        'delay' => $dao->delay,
        'ignore_condition_with_delay' => $dao->ignore_condition_with_delay,
        'is_active' => $dao->is_active,
        ];
    }
    return $result;
  }

  /**
   * Method om dao in array te stoppen en de 'overbodige' data er uit te slopen
   *
   * @param  $dao
   * @return array
   */
  public static function moveDaoToArray($dao) {
    $ignores = array('N', 'id', 'entity_id');
    $columns = get_object_vars($dao);
    // first remove all columns starting with _
    foreach ($columns as $key => $value) {
      if (substr($key, 0, 1) == '_') {
        unset($columns[$key]);
      }
      if (in_array($key, $ignores)) {
        unset($columns[$key]);
      }
    }
    return $columns;
  }

}

