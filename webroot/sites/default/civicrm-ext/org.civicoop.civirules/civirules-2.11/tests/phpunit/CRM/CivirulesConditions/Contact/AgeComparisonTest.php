<?php

/**
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 *
 * @group headless
 */
class CRM_CivirulesConditions_Contact_AgeComparisonTest extends CRM_Civirules_Test_TestCase {

  /**
   * Test a contact with nog age (equal must be false)
   */
  public function testNoAge() {
    $contactId = $this->contactId;
    // The standard test contact Adele Jensen has no known age .
    $triggerData = $this->getTriggerDataFromPost('Contact', $contactId, array('contact_id' => $contactId));

    $ruleCondition['condition_params'] = serialize(array(
      'entity' => 'civicrm_contact',
      'field' => 'birth_date',
      'operator' => '=',
      'value' => 56
    ));

    $condition = $this->conditionByName('contact_age_comparison');
    $condition->setRuleConditionData($ruleCondition);

    self::assertFalse($condition->isConditionValid($triggerData), 'No Age means condition must be false');

  }

  /**
   *    Test older than in combination with operator
   */
  public function testOlderThanFiftySix() {
    $contactId = $this->contactId;

    civicrm_api3('contact', 'create', array(
      'contact_id' => $contactId,
      'birth_date' => '19000101'
    ));

    $triggerData = $this->getTriggerDataFromPost('Contact', $contactId, array('contact_id' => $contactId));

    $ruleCondition['condition_params'] = serialize(array(
      'entity' => 'civicrm_contact',
      'field' => 'birth_date',
      'operator' => '>',
      'value' => 56
    ));

    $condition = $this->conditionByName('contact_age_comparison');
    $condition->setRuleConditionData($ruleCondition);

    self::assertTrue($condition->isConditionValid($triggerData), 'Adele must be older than 56');

  }


}
