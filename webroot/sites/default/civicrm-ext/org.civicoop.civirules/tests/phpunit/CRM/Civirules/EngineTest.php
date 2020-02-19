<?php

/**
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 *
 * @group headless
 */
class CRM_Civirules_EngineTest extends CRM_Civirules_Test_TestCase {

  /**
   *  Test if all the active triggers have a php class in the class loading path
   */
  public function testAllTriggersHaveCode() {
    $bao = new CRM_Civirules_BAO_Trigger();
    $bao->is_active = 1;
    $bao->find();
    while ($bao->fetch()) {
      $class_name = $bao->class_name;
      $name = $bao->name;
      if (isset($class_name)) {
        self::assertTrue(class_exists($class_name), "The $class_name class must exist for the active trigger with the name '$name' ");
      }
    }
  }

  /**
   * Test a trigger has a defined class
   */
  public function testAllTriggersHaveAClass() {
		// There are more triggers with empty class name. An empty class name means they will be triggered by the default post trigger.
		// So we should check for whether the class exists.
    $bao = new CRM_Civirules_BAO_Trigger();
    $bao->find();
    while ($bao->fetch()) {
    	// Try to get the class:
    	$class = CRM_Civirules_BAO_Trigger::getPostTriggerObjectByClassName($bao->class_name, false);
      $this->assertInstanceOf('CRM_Civirules_Trigger', $class, 'Could not instanciated trigger class for '.$bao->class_name);
    }
  }

  /**
   * Test if all the active conditions have a php class in the class loading path
   */
  public function testAllConditionsHaveCode() {
    $bao = new CRM_Civirules_BAO_Condition();
    $bao->is_active = 1;
    $bao->find();
    while ($bao->fetch()) {
      $class_name = $bao->class_name;
      $name = $bao->name;
      self::assertTrue(class_exists($class_name), "The $class_name class must exist for the active condition with the name '$name' ");
    }
  }

  /**
   * Test if all the active actions have a php class in the class loading path
   */
  public function testAllActionHaveCode() {
    $bao = new CRM_Civirules_BAO_Action();
    $bao->is_active = 1;
    $bao->find();
    while ($bao->fetch()) {
      $class_name = $bao->class_name;
      $name = $bao->name;
      self::assertTrue(class_exists($class_name), "The $class_name class must exist for the active action  with the name '$name' ");
    }
  }

  /**
   * Test the executing of the trigger for a creating a new contact (and ignoring an
   * update and a delete
   */
  public function testNewContact() {
    $this->setUpContactRule('new_contact');
    $this->assertRuleNotFired('new contact rule just set up, should not be fired');

    $result = civicrm_api3("Contact", "create", array(
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name' => 'Jensen',
    ));

    $contactId = $result['id'];

    $this->assertRuleFired("After an insert the rule should fire");

    $result = civicrm_api3("Contact", "create", array(
      'id' => $contactId,
      'nick_name' => 'A.',
    ));

    $this->assertRuleNotFired("The rule must be not fired after an update");

    civicrm_api3("Contact", "delete", array(
      'id' => $contactId,
    ));
    $this->assertRuleNotFired("The rule must be not fired after an delete");
  }

  /**
   * Test the firing of the trigger for a changed contact (and ignore the create and the delete
   */
  public function testChangedContact() {
    $this->setUpContactRule('changed_contact');
    $this->assertRuleNotFired('changed contact rule just set up, should not be fired');

    $result = civicrm_api3("Contact", "create", array(
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name' => 'Jensen',
    ));

    $contactId = $result['id'];

    $this->assertRuleNotFired("The change rule must not fire after an insert");
    $result = civicrm_api3("Contact", "create", array(
      'id' => $contactId,
      'nick_name' => 'A.',
    ));
    $this->assertRuleFired("The rule must be fired after an update");
    civicrm_api3("Contact", "delete", array(
      'id' => $contactId,
    ));
    $this->assertRuleNotFired("The rule must be not fired after a delete");
  }

  /**
   * Test the firing of the trigger for a changed contact (and ignore the create and the delete
   */
  public function testDeletedContact() {
    $this->setUpContactRule('deleted_contact');
    $this->assertRuleNotFired('changed contact rule just set up, should not be fired');

    $result = civicrm_api3("Contact", "create", array(
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name' => 'Jensen',
    ));

    $contactId = $result['id'];

    $this->assertRuleNotFired("The delete rule must not fire after an insert");

    $result = civicrm_api3("Contact", "create", array(
      'id' => $contactId,
      'nick_name' => 'A.',
    ));

    $this->assertRuleNotFired("The delete rule must not be fired after an update");

    $result = civicrm_api3("Contact", "delete", array(
      'id' => $contactId,
      'skip_undelete' => TRUE,
      // trigger fires alone for a real delete (trash does not count)
    ));

    $this->assertRuleFired("The delete rule must be fired after a delete");

  }
}
