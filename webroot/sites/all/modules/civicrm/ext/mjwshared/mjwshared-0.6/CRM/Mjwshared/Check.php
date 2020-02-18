<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

use CRM_Mjwshared_ExtensionUtil as E;

/**
 * Class CRM_Mjwshared_Check
 */
class CRM_Mjwshared_Check {

  public static function checkRequirements(&$messages) {
    $extensions = civicrm_api3('Extension', 'get', [
      'full_name' => 'uk.co.nfpservice.onlineworldpay',
    ]);

    if (!empty($extensions['id']) && ($extensions['values'][$extensions['id']]['status'] === 'installed')) {
      $messages[] = new CRM_Utils_Check_Message(
        'mjwshared_incompatible',
        E::ts('You have the uk.co.nfpservice.onlineworldpay extension installed.
        There are multiple versions of this extension on various sites and the source code has not been released.
        It is known to be cause issues with other payment processors and should be disabled'),
        E::ts('Incompatible Extension: uk.co.nfpservice.onlineworldpay'),
        \Psr\Log\LogLevel::WARNING,
        'fa-money'
      );
    }
  }

}
