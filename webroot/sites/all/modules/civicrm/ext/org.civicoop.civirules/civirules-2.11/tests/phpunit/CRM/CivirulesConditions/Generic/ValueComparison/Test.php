<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * @author John P Kirk (CiviFirst) <https://github.com/JohnFF>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @group headless
 */
class CRM_CivirulesConditions_Generic_ValueComparison_Test extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Test the 'string contains' operator
   */
  public function testStringContains() {
    $valueComparisonTest = new ReflectionClass('CRM_CivirulesConditions_Generic_ValueComparison');
    $compareMethod = $valueComparisonTest->getMethod('compare');
    $compareMethod->setAccessible(true);

    $testObject = $this->getMockForAbstractClass('CRM_CivirulesConditions_Generic_ValueComparison',
      array(),
      '',
      FALSE,
      TRUE,
      TRUE,
      array()
    );

    // Test the string on the left contains the string on the right.
    $this->assertTrue($compareMethod->invoke($testObject, 'test', 'test', 'contains string'));
    $this->assertFalse($compareMethod->invoke($testObject, 'false', 'truth', 'contains string'));
    $this->assertTrue($compareMethod->invoke($testObject, 'yes please', 'yes', 'contains string'));
    $this->assertTrue($compareMethod->invoke($testObject, 'Yes please', 'yes', 'contains string')); // Test caps.
  }

}
