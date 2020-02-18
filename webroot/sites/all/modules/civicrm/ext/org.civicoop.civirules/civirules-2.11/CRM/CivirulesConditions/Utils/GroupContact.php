<?php

class CRM_CivirulesConditions_Utils_GroupContact {

  /**
   * Checks wether a contact is a member of a group
   *
   * This function is a copy of CRM_Contact_BAO_GroupContact::isContactInGroup but with
   * a change so that the group contact cache won't be rebuild. Which somehow resulted
   * in a deadlock
   *
   * @param $contact_id
   * @param $group_id
   * @return bool
   */
  public static function isContactInGroup($contact_id, $group_id) {
    if (!CRM_Utils_Rule::positiveInteger($contact_id) ||
      !CRM_Utils_Rule::positiveInteger($group_id)
    ) {
      return FALSE;
    }
    try {
      $groupContactCount = civicrm_api3('GroupContact', 'getcount', [
        'group_id' => $group_id,
        'contact_id' => $contact_id,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    if ($groupContactCount > 0) {
      return TRUE;
    }
    return FALSE;
  }

}