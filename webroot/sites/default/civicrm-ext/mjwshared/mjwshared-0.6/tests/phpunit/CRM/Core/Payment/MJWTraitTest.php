<?php

use CRM_Mjwshared_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test trait functionalty
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
class CRM_Core_Payment_MJWTraitTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
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
   * Test getEmail
   */
  public function testGetEmail() {
    $t = new TheTrait();
    $billingLocationId = CRM_Core_BAO_LocationType::getBilling();

    // First test selection logic when emails are provided in the input array.
    $emails = [
      "email" => 'other@example.com',
      "email-Primary" => 'primary@example.com',
      "email-$billingLocationId" => 'billing@example.com',
    ];
    $this->assertEquals('billing@example.com',
      $t->getBillingEmail($emails, NULL)
    );


    array_pop($emails);
    $this->assertEquals('primary@example.com',
      $t->getBillingEmail($emails, NULL)
    );

    array_pop($emails);
    $this->assertEquals('other@example.com',
      $t->getBillingEmail($emails, NULL)
    );

    // Test that without a contact nor emails, we return null.
    $this->assertNull($t->getBillingEmail([], NULL));

    // Next test selection logic when emails are not in the input array.
    $contact_id = civicrm_api3('Contact', 'create', ['contact_type' => 'Individual', 'display_name' => 'test contact'])['id'];

    // It should return NULL for a contact that has no emails.
    $this->assertNull($t->getBillingEmail([], $contact_id));

    // It should be able to find a single email.
    $email_id_1 = civicrm_api3('Email', 'create', [
      'contact_id' => $contact_id,
      'email' => 'other@example.com',
    ])['id'];
    $this->assertEquals('other@example.com',
      $t->getBillingEmail([], $contact_id),
      'Failed looking up a single email for a contact'
    );

    // It should be able to find an email if a contact has 2+
    $email_id_2 = civicrm_api3('Email', 'create', [
      'contact_id' => $contact_id,
      'email' => 'another@example.com',
    ])['id'];
    $this->assertRegexp(
      '/^(an)?other@example.com$/',
      $t->getBillingEmail([], $contact_id),
      'Failed looking up an email for a contact that has more than one.'
    );

    // It should find a Primary email if one exists (and there's no billing one)
    $email_id_3 = civicrm_api3('Email', 'create', [
      'contact_id' => $contact_id,
      'email'      => 'primary@example.com',
      'is_primary' => 1,
    ])['id'];
    $this->assertEquals(
      'primary@example.com',
      $t->getBillingEmail([], $contact_id),
      'Failed to find the primary email amongst others.'
    );

    // It should find a billing email if one exists.
    $email_id_4 = civicrm_api3('Email', 'create', [
      'contact_id'       => $contact_id,
      'email'            => 'billing@example.com',
      'location_type_id' => $billingLocationId,
    ])['id'];
    $this->assertEquals(
      'billing@example.com',
      $t->getBillingEmail([], $contact_id),
      'Failed to find the billing email amongst others.'
    );

  }

}

class TheTrait {
  use CRM_Core_Payment_MJWTrait;
}
