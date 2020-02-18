<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests to test the action engine
 * 
 * @group headless
 */
class CRM_Civirules_ActionEngine_ActionEngineTest extends CRM_Civirules_Test_TestCase {

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testActionEngineExecutionWithoutAnyDelay() {
		// Fake the execution of an action AddContactToGroup
		$action_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM civirule_action WHERE name = 'add_contact_group'");
		$ruleAction = array(
			'id' => microtime(), // use time as a unique identifier
			'action_id' => $action_id,
			'action_params' => serialize(array('group_id' => $this->groupId)),
			'delay' => null,
			'ignore_condition_with_delay' => 0,
			'is_active' => 1,
		);
		
		$contact = civicrm_api3('Contact', 'getsingle', array('id' => $this->contactId));
		$triggerData = new CRM_Civirules_TriggerData_Post('Individual', $contact['id'], $contact);
		
		$actionEngine = CRM_Civirules_ActionEngine_Factory::getEngine($ruleAction, $triggerData);
		$this->assertInstanceOf('CRM_Civirules_ActionEngine_RuleActionEngine', $actionEngine, 'Could not find valud engine for rule_action');
		$actionEngine->execute();
		// Now test whether the contact is added to the group
		$groupContactParams = array(
      'contact_id' => $this->contactId,
      'group_id' => $this->groupId,
      'version' => 3,
    );
    $groupContact = civicrm_api('group_contact', 'getsingle', $groupContactParams);
		$this->assertEquals($this->groupId, $groupContact['group_id'], 'There was an error getting the group. Possibly the engine failed and the contact was not added to the group');
	}

  /**
   * Test processing of delayed actions.
   *
   * @throws \CiviCRM_API3_Exception
   */
  public function testExecuteDelayedAction() {
    // Fake the execution of an action AddContactToGroup
    $action_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM civirule_action WHERE name = 'add_contact_group'");
    $ruleAction = array(
      'id' => microtime(), // use time as a unique identifier
      'action_id' => $action_id,
      'action_params' => serialize(array('group_id' => $this->groupId)),
      'delay' => null,
      'ignore_condition_with_delay' => 1,
      'is_active' => 1,
    );

    $contact = civicrm_api3('Contact', 'getsingle', array('id' => $this->contactId));
    $triggerData = new CRM_Civirules_TriggerData_Post('Individual', $contact['id'], $contact);

    $actionEngine = CRM_Civirules_ActionEngine_Factory::getEngine($ruleAction, $triggerData);
    $this->assertInstanceOf('CRM_Civirules_ActionEngine_RuleActionEngine', $actionEngine, 'Could not find valid engine for rule_action');

    $ctx = new CRM_Queue_TaskContext();
    CRM_Civirules_Engine::executeDelayedAction($ctx, $actionEngine);

    // Now test whether the contact is added to the group
    $groupContactParams = array(
      'contact_id' => $this->contactId,
      'group_id' => $this->groupId,
      'version' => 3,
    );
    $groupContact = civicrm_api('group_contact', 'getsingle', $groupContactParams);
    $this->assertArrayHasKey('group_id', $groupContact, 'There was an error getting the group. Possibly the engine failed and the contact was not added to the group');
    $this->assertEquals($this->groupId, $groupContact['group_id'], 'There was an error getting the group. Possibly the engine failed and the contact was not added to the group');
  }

  /**
   * Test processing of delayed actions with the old parameter style, $ruleAction, $triggerData
   * This test exists because in a real installation which has been upgraded the delayed action queue
   * might still consists of actions defined the old way. We do want those to be executed as they always did.
   *
   * @throws \CiviCRM_API3_Exception
   */
  public function testExecuteDelayedActionOldStyle() {
    // Fake the execution of an action AddContactToGroup
    $action_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM civirule_action WHERE name = 'add_contact_group'");
    $ruleAction = array(
      'id' => microtime(), // use time as a unique identifier
      'action_id' => $action_id,
      'action_params' => serialize(array('group_id' => $this->groupId)),
      'delay' => null,
      'ignore_condition_with_delay' => 1,
      'is_active' => 1,
    );

    $action = CRM_Civirules_BAO_Action::getActionObjectById($ruleAction['action_id']);
    $action->setRuleActionData($ruleAction);
    $contact = civicrm_api3('Contact', 'getsingle', array('id' => $this->contactId));
    $triggerData = new CRM_Civirules_TriggerData_Post('Individual', $contact['id'], $contact);

    $ctx = new CRM_Queue_TaskContext();
    CRM_Civirules_Engine::executeDelayedAction($ctx, $action, $triggerData);

    // Now test whether the contact is added to the group
    $groupContactParams = array(
      'contact_id' => $this->contactId,
      'group_id' => $this->groupId,
      'version' => 3,
    );
    $groupContact = civicrm_api('group_contact', 'getsingle', $groupContactParams);
    $this->assertArrayHasKey('group_id', $groupContact, 'There was an error getting the group. Possibly the engine failed and the contact was not added to the group');
    $this->assertEquals($this->groupId, $groupContact['group_id'], 'There was an error getting the group. Possibly the engine failed and the contact was not added to the group');
  }


}