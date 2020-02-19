<?php
/**
 * https://civicrm.org/licensing
 */

use CRM_Stripe_ExtensionUtil as E;

/**
 * Class CRM_Stripe_Check
 */
class CRM_Stripe_Check {

  const MIN_VERSION_MJWSHARED = '0.6';

  public static function checkRequirements(&$messages) {
    $extensions = civicrm_api3('Extension', 'get', [
      'full_name' => "mjwshared",
    ]);

    if (empty($extensions['id']) || ($extensions['values'][$extensions['id']]['status'] !== 'installed')) {
      $messages[] = new CRM_Utils_Check_Message(
        'stripe_requirements',
        E::ts('The Stripe extension requires the mjwshared extension which is not installed (https://lab.civicrm.org/extensions/mjwshared).'),
        E::ts('Stripe: Missing Requirements'),
        \Psr\Log\LogLevel::ERROR,
        'fa-money'
      );
    }

    if (version_compare($extensions['values'][$extensions['id']]['version'], self::MIN_VERSION_MJWSHARED) === -1) {
      $messages[] = new CRM_Utils_Check_Message(
        'stripe_requirements',
        E::ts('The Stripe extension requires the mjwshared extension version %1 or greater but your system has version %2.',
          [
            1 => self::MIN_VERSION_MJWSHARED,
            2 => $extensions['values'][$extensions['id']]['version']
          ]),
        E::ts('Stripe: Missing Requirements'),
        \Psr\Log\LogLevel::ERROR,
        'fa-money'
      );
    }
  }

}
