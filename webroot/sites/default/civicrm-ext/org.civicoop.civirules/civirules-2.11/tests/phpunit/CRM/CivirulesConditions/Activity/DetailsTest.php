<?php

/**
 * @author Michael McAndrew (Third Sector Design) <michael@3sd.io>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 *
 * @group headless
 */
class CRM_CivirulesConditions_Activity_DetailsTest extends CRM_Civirules_Test_TestCase {

  /**
   * @dataProvider detailsProvider
   */
  public function testDetails($details, $operator, $text, $expected) {

    $result = civicrm_api3("Activity", "create",
     array(
       'source_contact_id' => $this->contactId,
       'activity_name' => 'Inbound SMS',
       'details' => $details,
     )
    );

    $activityId = $result['id'];

    $condition = $this->conditionByName('contact_has_activity_with_details');

    $ruleCondition['condition_params'] = serialize(array(
      'entity' => 'civicrm_activity',
      'field' => 'details',
      'operator' => $operator,
      'text' => $text
    ));

    $condition->setRuleConditionData($ruleCondition);

    $triggerData = $this->getTriggerDataFromPost('Activity', $activityId, $result['values'][$activityId]);

    self::assertEquals($expected, $condition->isConditionValid($triggerData));
  }

  public function detailsProvider(){
    return array(
      array('Elizabeth Bennet', 'contains', 'Bennet', true),
      array('Elizabeth Bennet', 'contains', 'Jane', false),
      array('Banana', 'exact_match', 'Banana', true), // exact match is case insensitive and trims both sides before comparison.
      array('Banana ', 'exact_match', 'Banana', true),
      array('Peach', 'exact_match', 'peach', true),
      array('Peach', 'exact_match', 'PEACH', true),
      array('Banana', 'exact_match', 'Peach', false),
    );
  }
}
