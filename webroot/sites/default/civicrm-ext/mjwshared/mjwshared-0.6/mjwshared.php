<?php
/**
 * https://civicrm.org/licensing
 */

require_once 'mjwshared.civix.php';
use CRM_Mjwshared_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mjwshared_civicrm_config(&$config) {
  _mjwshared_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mjwshared_civicrm_xmlMenu(&$files) {
  _mjwshared_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mjwshared_civicrm_install() {
  _mjwshared_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function mjwshared_civicrm_postInstall() {
  _mjwshared_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mjwshared_civicrm_uninstall() {
  _mjwshared_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mjwshared_civicrm_enable() {
  _mjwshared_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mjwshared_civicrm_disable() {
  _mjwshared_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mjwshared_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mjwshared_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mjwshared_civicrm_managed(&$entities) {
  _mjwshared_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mjwshared_civicrm_caseTypes(&$caseTypes) {
  _mjwshared_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function mjwshared_civicrm_angularModules(&$angularModules) {
  _mjwshared_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mjwshared_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mjwshared_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function mjwshared_civicrm_entityTypes(&$entityTypes) {
  _mjwshared_civix_civicrm_entityTypes($entityTypes);
}

/**
 * This hook is invoked when the 'confirm register' and 'thank you' form is rendered
 */
function mjwshared_civicrm_buildForm($formName, &$form) {
  switch ($formName) {
    case 'CRM_Event_Form_Registration_Register':
      CRM_Core_Region::instance('page-body')->add([
        'template' => 'CRM/Form/validate.tpl'
      ]);
  }
}

/**
 * Implements hook_civicrm_check().
 *
 * @throws \CiviCRM_API3_Exception
 */
function mjwshared_civicrm_check(&$messages) {
  CRM_Mjwshared_Check::checkRequirements($messages);
}
