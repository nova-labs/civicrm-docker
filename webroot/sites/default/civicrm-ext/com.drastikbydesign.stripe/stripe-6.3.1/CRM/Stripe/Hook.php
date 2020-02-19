<?php
/**
 * https://civicrm.org/licensing
 */

/**
 * This class implements hooks for Stripe
 */
class CRM_Stripe_Hook {

  /**
   * This hook allows modifying recurring contribution parameters
   *
   * @param array $recurContributionParams Recurring contribution params (ContributionRecur.create API parameters)
   *
   * @return mixed
   */
  public static function updateRecurringContribution(&$recurContributionParams) {
    return CRM_Utils_Hook::singleton()
      ->invoke(1, $recurContributionParams, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject,
        CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, 'civicrm_stripe_updateRecurringContribution');
  }

}
