<?php
/**
 * @author Véronique Gratioulet <veronique.gratioulet@atd-quartmonde.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
/**
 * Trigger when an Organization Custom Data changes.

 */
class CRM_CivirulesPostTrigger_OrganizationCustomDataChanged extends CRM_CivirulesPostTrigger_ContactCustomDataChanged {

  protected static function getObjectName() {
    return 'Organization';
  }

}