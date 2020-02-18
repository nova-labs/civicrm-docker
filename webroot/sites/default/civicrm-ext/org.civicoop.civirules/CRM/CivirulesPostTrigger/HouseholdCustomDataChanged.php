<?php
/**
 * @author Véronique Gratioulet <veronique.gratioulet@atd-quartmonde.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
/**
 * Trigger when Household Custom Data changes.

 */
class CRM_CivirulesPostTrigger_HouseholdCustomDataChanged extends CRM_CivirulesPostTrigger_ContactCustomDataChanged {

  protected static function getObjectName() {
    return 'Household';
  }
}
