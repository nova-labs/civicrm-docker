<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_Civirules_Config
{
  /*
   * singleton pattern
   */
  static private $_singleton = NULL;
  /*
   * properties to hold the valid entities and actions for civirule trigger
   */
  protected $validTriggerObjectNames = NULL;
  protected $validTriggerOperations = NULL;

  /**
   * Constructor
   */
  function __construct()   {
    $this->setTriggerProperties();
  }

  /**
   * Function to return singleton object
   *
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton()
  {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Civirules_Config();
    }
    return self::$_singleton;
  }

  /**
   * Function to get the valid trigger entities
   *
   * @return int
   * @access public
   */
  public function getValidTriggerObjectNames()
  {
    return $this->validTriggerObjectNames;
  }

  /**
   * Function to get the valid trigger actions
   *
   * @return int
   * @access public
   */
  public function getValidTriggerOperations()
  {
    return $this->validTriggerOperations;
  }

  protected function setTriggerProperties() {
    $this->validTriggerOperations = array(
      'create',
      'edit',
      'delete',
      'restore',
      'trash',
      'update');

    // Load all entities from CiviCRM core.
    $this->validTriggerObjectNames = array_keys(CRM_Core_DAO_AllCoreTables::daoToClass());
    $this->validTriggerObjectNames[] = 'Individual';
    $this->validTriggerObjectNames[] = 'Household';
    $this->validTriggerObjectNames[] = 'Organization';
  }
}