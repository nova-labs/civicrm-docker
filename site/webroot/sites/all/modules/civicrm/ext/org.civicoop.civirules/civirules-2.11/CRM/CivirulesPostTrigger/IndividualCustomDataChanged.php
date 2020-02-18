<?php
/**
 * @author Véronique Gratioulet <veronique.gratioulet@atd-quartmonde.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
/**
 * Trigger when an Individual Custom Data changes.

 */
class CRM_CivirulesPostTrigger_IndividualCustomDataChanged extends CRM_CivirulesPostTrigger_ContactCustomDataChanged {

  protected static function getObjectName() {
    return 'Individual';
  }
}