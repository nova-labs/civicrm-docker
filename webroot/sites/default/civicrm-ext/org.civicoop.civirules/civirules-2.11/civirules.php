<?php

require_once 'civirules.civix.php';
if (!interface_exists("\\Psr\\Log\\LoggerInterface")) {
  require_once('psr/log/LoggerInterface.php');
}
if (!class_exists("\\Psr\\Log\\LogLevel")) {
  require_once('psr/log/LogLevel.php');
}

use CRM_Civirules_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civirules_civicrm_config(&$config) {
  _civirules_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civirules_civicrm_xmlMenu(&$files) {
  _civirules_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civirules_civicrm_install() {
  return _civirules_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civirules_civicrm_postInstall() {
  _civirules_civix_civicrm_postInstall();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civirules_civicrm_uninstall() {
  return _civirules_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civirules_civicrm_enable() {
  return _civirules_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civirules_civicrm_disable() {
  return _civirules_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civirules_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civirules_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civirules_civicrm_managed(&$entities) {
  // First create a backup because the managed entities are gone
  // so the actions and conditions table are first going to be emptied
  _civirules_upgrade_to_2x_backup();
  // Check triggers, actions and conditions
  CRM_Civirules_Utils_Upgrader::insertTriggersFromJson(E::path('sql/triggers.json'));
  CRM_Civirules_Utils_Upgrader::insertActionsFromJson(E::path('sql/actions.json'));
  CRM_Civirules_Utils_Upgrader::insertConditionsFromJson(E::path('sql/conditions.json'));
  return _civirules_civix_civicrm_managed($entities);
}

/**
 * Helper function to create a backup if the current schema version is of a 1.x version.
 * We need this backup to restore missing actions and rules after upgrading.
 */
function _civirules_upgrade_to_2x_backup() {
  // Check schema version
  // Schema version 1023 is inserted by a 2x version
  // So if the schema version is lower than 1023 we are still on a 1x version.
  $schemaVersion = CRM_Core_DAO::singleValueQuery("SELECT schema_version FROM civicrm_extension WHERE `name` = 'CiviRules'");
  if ($schemaVersion >= 1023) {
    return; // No need for preparing the update.
  }

  if (!CRM_Core_DAO::checkTableExists('civirule_rule_action_backup')) {
    // Backup the current action and condition connected to a civirule
    CRM_Core_DAO::executeQuery("
      CREATE TABLE `civirule_rule_action_backup` 
      SELECT `civirule_rule_action`.*, `civirule_action`.`class_name` as `action_class_name` 
      FROM `civirule_rule_action` 
      INNER JOIN `civirule_action` ON `civirule_rule_action`.`action_id` = `civirule_action`.`id` 
    ");
  }
  if (!CRM_Core_DAO::checkTableExists('civirule_rule_action_backup')) {
    CRM_Core_DAO::executeQuery("
      CREATE TABLE `civirule_rule_condition_backup`
      SELECT `civirule_rule_condition`.*, `civirule_condition`.`class_name` as `condition_class_name` 
      FROM `civirule_rule_condition` 
      INNER JOIN `civirule_condition` ON `civirule_rule_condition`.`condition_id` = `civirule_condition`.`id` 
    ");
  }
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civirules_civicrm_caseTypes(&$caseTypes) {
  _civirules_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civirules_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civirules_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook civicrm_navigationMenu
 * to create a CiviRules menu item in the Administer menu
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function civirules_civicrm_navigationMenu( &$params ) {
  // Get the maximum key of $params
  $maxKey = CRM_Civirules_Utils::getMenuKeyMax($params);
  $newNavId = $maxKey + 1;
  // retrieve the option group id of the rule tags option group
  $optionGroup = CRM_Civirules_Utils_OptionGroup::getSingleWithName('civirule_rule_tag');
  // retrieve the id of the "Administer" menu item
  foreach($params as $key => $item) {
  	if (isset($item['attributes']['name']) && $item['attributes']['name'] === 'Administer') {
  	  $administerID = $item['attributes']['navID'];
  	}
  }
  $params[$administerID]['child'][$newNavId] = array(
    'attributes' => array(
      'label' => 'CiviRules',
      'name' => 'CiviRules',
      'url' => NULL,
      'permission' => 'administer CiviCRM',
      'operator' => NULL,
      'separator' => NULL,
      'parentID' => $administerID,
      'navID' => $newNavId,
      'active' => 1
    ));
	$parentId = $newNavId;
	$newNavId++;
  // add child menu for manage rules
  $params[$administerID]['child'][$parentId]['child'][$newNavId] = array(
    'attributes' => array(
      'label' => ts('Manage Rules'),
      'name' => ts('Manage Rules'),
      'url' => CRM_Utils_System::url('civicrm/civirules/form/rulesview', 'reset=1', TRUE),
      'permission' => 'administer CiviCRM',
      'operator' => NULL,
      'separator' => 0,
      'parentID' => $parentId,
      'navID' => $newNavId,
      'active' => 1
    ),
    'child' => NULL
  );
  $newNavId++;
  $params[$administerID]['child'][$parentId]['child'][$newNavId] = array(
    'attributes' => array(
      'label' => ts('New Rule'),
      'name' => ts('New Rule'),
      'url' => CRM_Utils_System::url('civicrm/civirule/form/rule', 'reset=1&action=add', TRUE),
      'permission' => 'administer CiviCRM',
      'operator' => NULL,
      'separator' => 0,
      'parentID' => $parentId,
      'navID' => $newNavId,
      'active' => 1
    ),
    'child' => NULL
  );
  $newNavId++;
  // add child menu for rule tags if option group id set with version check because 4.4 has other url pattern
  if (isset($optionGroup['id']) && !empty($optionGroup['id'])) {
    try {
      $apiVersion = civicrm_api3('Domain', 'getvalue', array('current_domain' => "TRUE", 'return' => 'version'));
      $civiVersion = (float) substr($apiVersion, 0, 3);
      if ($civiVersion < 4.6) {
        $ruleTagUrl = CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&gid='.$optionGroup['id'], TRUE);
      } else {
        $ruleTagUrl = CRM_Utils_System::url('civicrm/admin/options', 'reset=1&gid='.$optionGroup['id'], TRUE);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $ruleTagUrl = CRM_Utils_System::url('civicrm/admin/options', 'reset=1&gid='.$optionGroup['id'], TRUE);
    }

    $params[$administerID]['child'][$parentId]['child'][$newNavId] = array(
      'attributes' => array (
        'label'      => ts('CiviRule Tags'),
        'name'       => ts('CiviRules Tags'),
        'url'        => $ruleTagUrl,
        'permission' => 'administer CiviCRM',
        'operator'   => NULL,
        'separator'  => 0,
        'parentID'   => $parentId,
        'navID'      => $newNavId,
        'active'     => 1
      ),
      'child' => NULL
    );
		$newNavId++;
  }
}

function civirules_civicrm_pre($op, $objectName, $objectId, &$params) {
  CRM_Civirules_Utils_PreData::pre($op, $objectName, $objectId, $params);
  CRM_Civirules_Utils_CustomDataFromPre::pre($op, $objectName, $objectId, $params);
}

function civirules_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  CRM_Civirules_Trigger_Post::post($op, $objectName, $objectId, $objectRef);
}

function civirules_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  CRM_CivirulesPostTrigger_CaseCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_ContactCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_IndividualCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_OrganizationCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_HouseholdCustomDataChanged::validateForm($form);
}

function civirules_civicrm_custom($op, $groupID, $entityID, &$params) {
  /**
   * Fix/Hack for issue #208 (https://github.com/CiviCooP/org.civicoop.civirules/issues/208)
   *
   * To reproduce:
   * - create a custom data set for contacts that supports multiple records
   * - create a rule that triggers on custom data changing
   * - add a record to that custom data set for a contact
   * - delete the record
   * - observe the logs
   *
   * This returns the error: "Expected one Contact but found 25"
   * Traced to CRM/CivirulesPostTrigger/ContactCustomDataChanged.php where there is an api call to contacts getsingle. The issue is that when the custom data record is deleted, there is no remaining entity_id with which to retrieve the contact, and so no id is passed to the getsingle call.
   *
   * The fix is to check whether the $op is delete and whether $entityID is empty and then check
   * whether the contactID is provided in the url.
   */
  if ($op == 'delete' && empty($entityID)) {
    $contactId = CRM_Utils_Request::retrieve('contactId', 'Positive');
    if (!empty($contactId)) {
      $entityID = $contactId;
    }
  }
  /** End ugly hack */

  CRM_CivirulesPostTrigger_CaseCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_ContactCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_IndividualCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_OrganizationCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_HouseholdCustomDataChanged::custom($op, $groupID, $entityID, $params);
}

function civirules_civirules_alter_trigger_data(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
  //also add the custom data which is passed to the pre hook (and not the post)
  CRM_Civirules_Utils_CustomDataFromPre::addCustomDataToTriggerData($triggerData);
}

/**
 * Register extensions entities.
 *
 * Required for api calls.
 *
 * @param array $entityTypes
 */
function civirules_civicrm_entityTypes(&$entityTypes) {
  $entityTypes['CRM_Civirules_BAO_Action'] = array(
    'name' => 'CiviRuleAction',
    'class' => 'CRM_Civirules_BAO_Action',
    'table' => 'civirule_action',
  );
  $entityTypes['CRM_Civirules_BAO_Condition'] = array(
    'name' => 'CiviRuleCondition',
    'class' => 'CRM_Civirules_BAO_Condition',
    'table' => 'civirule_condition',
  );
  $entityTypes['CRM_Civirules_BAO_Trigger'] = array(
    'name' => 'CiviRuleTrigger',
    'class' => 'CRM_Civirules_BAO_Trigger',
    'table' => 'civirule_trigger',
  );
  $entityTypes['CRM_Civirules_BAO_Rule'] = array(
    'name' => 'CiviRuleRule',
    'class' => 'CRM_Civirules_BAO_Rule',
    'table' => 'civirule_rule',
  );
}

/**
 * Implements hook_civicrm_apiWrappers()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_apiWrappers/
 */
function civirules_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'Contact' && $apiRequest['action'] == 'create') {
    $wrappers[] = new CRM_Civirules_TrashRestoreApiWrapper();
  }
}
