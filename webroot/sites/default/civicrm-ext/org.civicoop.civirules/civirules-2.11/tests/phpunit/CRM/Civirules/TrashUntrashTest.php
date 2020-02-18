<?php
/**
 * @author Klaas Eikelboom (klaas.eikelboom@civicoop.org)
 * @date 12-6-18
 * @license AGPL-3.0
 *
 *  @group headless
 */

class CRM_Civirules_TrashUntrashTest extends CRM_Civirules_Test_TestCase {

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testTrashedContact() {

    $this->setUpContactRule('trashed_contact');

    /* Create contact to trash - sorry Adele */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name' => 'Jensen',
    ]);

    $contactId = $result['id'];

    // simulate delete with contact

    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 1]);

    $this->assertRuleFired("After a simulated trash the rule must be fired");

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleFired("After the trash the rule should be fired");

    // a delete with a second parameters as true is actually a restore
    CRM_Contact_BAO_Contact::deleteContact($contactId,TRUE);

    $this->assertRuleNotFired("After a restore the rule must not be fired");

  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testRestoredContact() {

    $this->setUpContactRule('restored_contact');

    /* Create contact to trash - sorry Adele */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name' => 'Jensen',
    ]);

    $contactId = $result['id'];

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleNotFired("After the trash the restored_contact should not be fired");

    // a delete with a second parameters as true is actually a restore
    CRM_Contact_BAO_Contact::deleteContact($contactId,TRUE);

    $this->assertRuleFired("After a restore the restore rule must be fired");

    // now delete again and do a simulated restore

    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 1]);
    $this->assertRuleNotFired("A simulated delete must not fire the rule");
    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 0]);
    $this->assertRuleFired("A simulated restore must fire the rule");

  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testTrashedIndividual() {

    $this->setUpContactRule('trashed_individual');

    /* Create individual to trash - sorry Adele */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name' => 'Jensen',
    ]);

    $contactId = $result['id'];

    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 1]);

    /*
     the rule is not fired a direct update on the is_deleted column
     (not sure if this is right
    */
    $this->assertRuleFired('After the simulated trash of an individual a rule must fired');

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleFired('After an update on delete the rule should be fired');

    // a delete with a second parameters as true is actually a restore
    CRM_Contact_BAO_Contact::deleteContact($contactId,TRUE);

    $this->assertRuleNotFired("After a restore the trash rule must be skipped");
  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testRestoredIndividual() {

    $this->setUpContactRule('restored_individual');

    /* Create individual to trash - sorry Adele */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name' => 'Jensen',
    ]);

    $contactId = $result['id'];

    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 1]);

    /*
     the rule is not fired a direct update on the is_deleted column
     (not sure if this is right
    */
    $this->assertRuleNotFired('After an update on delete the rule is not fired');

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleNotFired("After the trash the rule should not be fired");

    // a delete with a second parameters as true is actually a restore
    CRM_Contact_BAO_Contact::deleteContact($contactId,TRUE);

    $this->assertRuleFired("After a restore the trash rule must be executed");

    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 1]);
    $this->assertRuleNotFired("A simulated delete must not fire the rule");
    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 0]);
    $this->assertRuleFired("A simulated restore must not fire the rule");
  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testTrashedHouseHold() {

    $this->setUpContactRule('trashed_household');

    /* Create contact to trash - sorry Adele */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Household',
      'household_name' => 'Fam Jensen'
    ]);

    $contactId = $result['id'];

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleFired("After the trash the rule should be fired");

    // a delete with a second parameters as true is actually a restore
    CRM_Contact_BAO_Contact::deleteContact($contactId,TRUE);

    $this->assertRuleNotFired("After a restore the rule must not be fired");

  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testRestoredHouseHold() {

    $this->setUpContactRule('restored_household');

    /* Create contact to trash - sorry Adele */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Household',
      'household_name' => 'Fam Jensen'
    ]);

    $contactId = $result['id'];

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleNotFired("After the trash the restore rule must not be fired");

    // a delete with a second parameters as true is actually a restore
    CRM_Contact_BAO_Contact::deleteContact($contactId,TRUE);

    $this->assertRuleFired("After a restore the restore rule must be fired");

    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 1]);
    $this->assertRuleNotFired("A simulated delete must not fire the rule");
    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 0]);
    $this->assertRuleFired("A simulated restore must not fire the rule");

  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testTrashedOrganization() {

    $this->setUpContactRule('trashed_organization');

    /* Create contact to trash - sorry Adele Investement Banking */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Organization',
      'organization_name' => 'Adele Investment Banking',
    ]);

    $contactId = $result['id'];

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleFired("After the trash of an organization the rule should be fired");
  }

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function testRestoredOrganization() {

    $this->setUpContactRule('restored_organization');

    /* Create contact to trash - sorry Adele Investement Banking */

    $result = civicrm_api3("Contact", "create", [
      'contact_type' => 'Organization',
      'organization_name' => 'Adele Investment Banking',
    ]);

    $contactId = $result['id'];

    civicrm_api3('Contact','delete',['id' => $contactId]);

    $this->assertRuleNotFired("After the trash of an organization the restore rule must not fired");

    // a delete with a second parameters as true is actually a restore
    CRM_Contact_BAO_Contact::deleteContact($contactId,TRUE);

    $this->assertRuleFired("After the restore of an organization the restore rule must be fired");

    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 1]);
    $this->assertRuleNotFired("A simulated delete must not fire the rule");
    civicrm_api3('Contact','create',['id' => $contactId, 'is_deleted' => 0]);
    $this->assertRuleFired("A simulated restore must fire the rule");
  }

}