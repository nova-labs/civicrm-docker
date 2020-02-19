<?php

/**
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 *
 * @group headless
 */
class CRM_CivirulesConditions_Contact_ContactInGroupTest extends CRM_Civirules_Test_TestCase {

  public function testContactIsInGroup() {

    $contactId = $this->contactId;
    $triggerData = $this->getTriggerDataFromPost('Contact', $contactId, array('contact_id' => $contactId));

    self::assertFalse(CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($contactId, $this->groupId), "before group create, contact should not be in group");

    $result = civicrm_api3('GroupContact', 'create', array(
      'group_id' => $this->groupId,
      'contact_id' => $contactId,
    ));

    self::assertTrue(CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($contactId, $this->groupId), "after group create, contact must be group");

    $ruleCondition['condition_params'] = serialize(array(
      'operator' => 'in all of',
      'group_ids' => array($this->groupId),
    ));

    $condition = $this->conditionByName('contact_in_group');
    $condition->setRuleConditionData($ruleCondition);

    self::assertTrue($condition->isConditionValid($triggerData));
  }

}
