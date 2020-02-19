<?php
/**
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 *
 * @group headless
 */
class CRM_CivirulesConditions_Utils_GroupContactTest extends CRM_Civirules_Test_TestCase {
  public function testIsContactInGroup() {
    
    self::assertFalse(CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($this->contactId, $this->groupId), "Before api call, contact should not be in group");
    $result = civicrm_api3('GroupContact', 'create', array(
      'group_id' => $this->groupId,
      'contact_id' => $this->contactId,
    ));
    self::assertTrue(CRM_CivirulesConditions_Utils_GroupContact::isContactInGroup($this->contactId, $this->groupId), "After api call, contact must be in group");
  }

}
