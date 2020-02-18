<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
abstract class CRM_Civirules_Test_TestCase extends \PHPUnit\Framework\TestCase implements HeadlessInterface, TransactionalInterface {

  protected $contactId;
  protected $groupId;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {

    $result = civicrm_api3("Contact","create",array(
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name'  => 'Jensen'
    ));
    $this->contactId=$result['id'];

    $result = civicrm_api3('Group','create',array(
      'title' => "TestGroup",
      'name' => "test_group",
    ));
    $this->groupId = $result['id'];

    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  protected function setUpContactRule($ruleName){

    $triggerId = civicrm_api3('CiviRuleTrigger', 'getvalue', array(
      'name' => $ruleName,
      'return' => "id",
    ));

    try {
      $actionId = civicrm_api3('CiviRuleAction', 'getsingle', array('class_name' => "CRM_CivirulesActions_Generic_Log", 'return' => array("id"),));
    } catch (CiviCRM_API3_Exception $e) {
      $result = civicrm_api3('CiviRuleAction','create',array (
        'name' => 'Log',
        'label' => 'Logs the trigger contents to the standard log',
        'class_name' => 'CRM_CivirulesActions_TestAction',
        'is_active' => 1
      ));
      $actionId = $result['id'];
    }

    $result = civicrm_api3('CiviRuleRule', 'create', array(
      'name' => "MyTestRule",
      'label' => "MyTestRule",
      'trigger_id' => $triggerId,
      'is_active' => 1,
    ));

    $ruleId = $result['id'];

    $params = array(
      'rule_id' => $ruleId,
      'action_id' => $actionId,
      'ignore_condition_with_delay' => 0,
      'action_params' => serialize(array('is_enabled' => 0, 'print_r_enabled' => 0 ))
    );

    $bao = new CRM_Civirules_BAO_RuleAction();
    $bao::add($params);
  }

  private function hasTestRuleFired(){
    $ruleId = civicrm_api3('CiviRuleRule', 'getvalue', array(
      'name' => "MyTestRule",
      'return' => "id",
    ));

    $sql = "SELECT count(1) FROM `civirule_rule_log` WHERE rule_id=%1";
    $params[1]=array($ruleId,'Integer');
    if(CRM_Core_DAO::singleValueQuery($sql, $params)){
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public static function getTriggerDataFromPost($objectName, $objectId, $data){

    $entity = CRM_Civirules_Utils_ObjectName::convertToEntity($objectName);
    $triggerData = new CRM_Civirules_TriggerData_Post($entity, $objectId, $data);
    return $triggerData;

  }

  public static function conditionByName($name){
    $conditionId = $result = civicrm_api3('CiviRuleCondition', 'getvalue', array(
      'return' => "id",
      'name' => $name,
    ));
    return CRM_Civirules_BAO_Condition::getConditionObjectById($conditionId);
  }


  private function cleanRuleLog(){
    $sql = "DELETE FROM `civirule_rule_log`";
    CRM_Core_DAO::executeQuery($sql);
  }

  protected function assertRuleFired($message){
    self::assertTrue($this->hasTestRuleFired(),$message);
    $this->cleanRuleLog();
  }

  protected function assertRuleNotFired($message){
    self::assertFalse($this->hasTestRuleFired(),$message);
    $this->cleanRuleLog();
  }

}
