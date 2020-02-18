<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * @author Patrick Figel (Greenpeace CEE)
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @group headless
 */
class CRM_CivirulesConditions_FieldValueComparisonTest extends CRM_Civirules_Test_TestCase {

  /**
   * Test basic condition against original & new data
   */
  public function testBasicCondition() {
    $entity = CRM_Civirules_Utils_ObjectName::convertToEntity('Contact');
    $triggerData = new CRM_Civirules_TriggerData_Edit(
      $entity,
      $this->contactId,
      [
        'contact_id' => $this->contactId,
        'first_name' => 'Jane',
      ],
      [
        'contact_id' => $this->contactId,
        'first_name' => 'Janette',
      ]
    );

    $condition = $this->conditionByName('field_value_comparison');

    $conditionParams = [
      'entity'         => 'Contact',
      'field'          => 'first_name',
      'operator'       => '=',
      'value'          => 'Jane',
      'original_data' => 0,
    ];
    $ruleCondition['condition_params'] = serialize($conditionParams);
    $condition->setRuleConditionData($ruleCondition);

    $this->assertTrue(
      $condition->isConditionValid($triggerData),
      'new value for first_name should be "Jane"'
    );

    $conditionParams['original_data'] = 1;
    $conditionParams['value'] = 'Janette';
    $ruleCondition['condition_params'] = serialize($conditionParams);
    $condition->setRuleConditionData($ruleCondition);

    $this->assertTrue(
      $condition->isConditionValid($triggerData),
      'original value for first_name should be "Janette"'
    );
  }

  /**
   * Test condition without the original_data flag
   */
  public function testNoOriginalValueFlag() {
    $entity = CRM_Civirules_Utils_ObjectName::convertToEntity('Contact');
    $triggerData = new CRM_Civirules_TriggerData_Edit(
      $entity,
      $this->contactId,
      [
        'contact_id' => $this->contactId,
        'do_not_email' => 1,
      ],
      [
        'contact_id' => $this->contactId,
        'do_not_email' => 0,
      ]
    );

    $ruleCondition['condition_params'] = serialize([
      'entity'   => 'Contact',
      'field'    => 'do_not_email',
      'operator' => '=',
      'value'    => 1
    ]);

    $condition = $this->conditionByName('field_value_comparison');
    $condition->setRuleConditionData($ruleCondition);

    $this->assertTrue(
      $condition->isConditionValid($triggerData),
      'new value for do_not_email should be 1'
    );
  }


}