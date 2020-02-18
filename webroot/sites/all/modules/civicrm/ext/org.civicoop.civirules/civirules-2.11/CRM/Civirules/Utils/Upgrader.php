<?php

/**
 * Util functions for upgrading
 */
class CRM_Civirules_Utils_Upgrader {

  /**
   * Method to check if conditions are in the DB and insert if not
   */
  public static function checkCiviRulesActions() {
    $actions = array(
      'CRM_CivirulesActions_Activity_Add' => array(
        'label' => 'Add activity to Contact',
        'name' => 'activity_add',
      ),
      'CRM_CivirulesActions_Activity_UpdateStatus' => array(
        'label' => 'Update Activity Status',
        'name' => 'activity_update_status',
      ),
      'CRM_CivirulesActions_Case_SetDateFieldOnCase' => array(
        'label' => 'Set a Date Field on a Case',
        'name' => 'set_date_field_on_case',
      ),
      'CRM_CivirulesActions_Case_SetStatus' => array(
        'label' => 'Set Status of a Case',
        'name' => 'set_case_status',
      ),
      'CRM_CivirulesActions_Contact_SetCommPref' => array(
        'label' => 'Set Communication Preferences of a Contact',
        'name' => 'set_contact_communication_preferences',
      ),
      'CRM_CivirulesActions_Contact_SetJobTitle' => array(
        'label' => 'Set Job Title of a Contact',
        'name' => 'set_contact_job_title',
      ),
      'CRM_CivirulesActions_Contact_SetPrivacyOptions' => array(
        'label' => 'Set Privacy Options of a Contact',
        'name' => 'set_contact_privacy_options',
      ),
      'CRM_CivirulesActions_Contact_SoftDelete' => array(
        'label' => 'Soft Delete a Contact',
        'name' => 'contact_soft_delete',
      ),
      'CRM_CivirulesActions_Contact_Subtype' => array(
        'label' => 'Set Subtype for a Contact',
        'name' => 'set_contact_sub_type',
      ),
      'CRM_CivirulesActions_Contribution_ThankYouDate' => array(
        'label' => 'Set Thank You Date for a Contribution',
        'name' => 'set_contribution_thank_date',
      ),
      'CRM_CivirulesActions_GroupContact_Add' => array(
        'label' => 'Add Contact to a Group',
        'name' => 'add_contact_group',
      ),
      'CRM_CivirulesActions_GroupContact_Remove' => array(
        'label' => 'Remove Contact from a Group',
        'name' => 'remove_contact_group',
      ),
      'CRM_CivirulesActions_Tag_Add' => array(
        'label' => 'Add Tag to a Contact',
        'name' => 'add_tag_contact',
      ),
      'CRM_CivirulesActions_Tag_Remove' => array(
        'label' => 'Remove Tag from a Contact',
        'name' => 'remove_tag_contact',
      ),
      'CRM_CivirulesActions_User_DisplayMessage' => array(
        'label' => 'Shows a Message to CiviCRM Users',
        'name' => 'display_user_message',
      ),
      'CRM_CivirulesActions_CreateDonor' => array(
        'label' => 'Set Contact as Donor',
        'name' => 'set_contact_donor',
      ),
    );
    foreach ($actions as $className => $actionData) {
      $select = "SELECT COUNT(*) FROM civirule_action WHERE class_name = %1";
      $count = CRM_Core_DAO::singleValueQuery($select, array(1 => array($className, 'String')));
      if ($count == 0) {
        $insert = "INSERT INTO civirule_action (name, label, class_name, is_active) VALUES(%1, %2, %3, %4)";
        CRM_Core_DAO::executeQuery($insert, array(
          1 => array($actionData['name'], 'String'),
          2 => array($actionData['label'], 'String'),
          3 => array($className, 'String'),
          4 => array(1, 'Integer'),
        ));
      }
    }
  }

  /**
   * Method to check if conditions are in the DB and insert if not
   */
  public static function checkCiviRulesConditions() {
    // conditions that should be there with class_name => name, label
    $conditions = array(
      'CRM_CivirulesConditions_Activity_Campaign' => array(
        'label' => 'Activity is (not) in Campaign(s)',
        'name' => 'activity_in_campaign',
      ),
      'CRM_CivirulesConditions_Activity_Details' => array(
        'label' => 'Activity Details',
        'name' => 'contact_has_activity_with_details',
      ),
      'CRM_CivirulesConditions_Activity_OnlyOnce' => array(
        'label' => 'Execute Action only Once for Activity',
        'name' => 'once_for_activity',
      ),
      'CRM_CivirulesConditions_Activity_RecordType' => array(
        'label' => 'Activity Contact Record Type (Assignee, Source or Target)',
        'name' => 'contact_activity_record_type',
      ),
      'CRM_CivirulesConditions_Activity_Status' => array(
        'label' => 'Activity Status is (not) one of',
        'name' => 'activity_status'
      ),
      'CRM_CivirulesConditions_Activity_StatusChanged' => array(
        'label' => 'Compare Old Activity Status to New Activity Status',
        'name' => 'activity_status_changed',
      ),
      'CRM_CivirulesConditions_Activity_Type' => array(
        'label' => 'Activity is (not) one of Type(s)',
        'name' => 'activity_of_type',
      ),
      'CRM_CivirulesConditions_Address_IsUnique' => array(
        'label' => 'Address is Unique',
        'name' => 'address_is_unique',
      ),
      'CRM_CivirulesConditions_Case_CaseActivity' => array(
        'label' => 'Days since Last Case Activity',
        'name' => 'case_activity_days',
      ),
      'CRM_CivirulesConditions_Case_CaseStatus' => array(
        'label' => 'Case Status is (not) one of',
        'name' => 'case_status',
      ),
      'CRM_CivirulesConditions_Case_CaseType' => array(
        'label' => 'Case is (not) one of Type(s)',
        'name' => 'case_type',
      ),
      'CRM_CivirulesConditions_Case_IsClient' => array(
        'label' => 'Is Client of the Case',
        'name' => 'is_case_client',
      ),
      'CRM_CivirulesConditions_Case_OnlyOnce' => array(
        'label' => 'Execute Action only Once for Case',
        'name' => 'only_once_for_case',
      ),
      'CRM_CivirulesConditions_Case_RelationshipIsCaseRole' => array(
        'label' => 'Relationship is a Case Role',
        'name' => 'relationship_is_case_role',
      ),
      'CRM_CivirulesConditions_Case_StatusChanged' => array(
        'label' => 'Compare Old Case Status to New Case Status',
        'name' => 'case_status_changed',
      ),
      'CRM_CivirulesConditions_Contact_AgeComparison' => array(
        'label' => 'Contact has Age',
        'name' => 'contact_age_comparison',
      ),
      'CRM_CivirulesConditions_Contact_BirthdayChanged' => array(
        'label' => 'Birthday has Changed',
        'name' => 'contact_birthday_changed',
      ),
      'CRM_CivirulesConditions_Contact_HasActivityInCampaign' => array(
        'label' => 'Contact has Activity of Type(s) in Campaign(s)',
        'name' => 'contact_has_activity_in_campaign',
      ),
      'CRM_CivirulesConditions_Contact_HasBeenInGroup' => array(
        'label' => 'Contact Has (Never) Been in Group',
        'name' => 'contact_has_been_in_group',
      ),
      'CRM_CivirulesConditions_Contact_HasPhone' => array(
        'label' => 'Contact Has Phone',
        'name' => 'contact_has_phone',
      ),
      'CRM_CivirulesConditions_Contact_HasSubtype' => array(
        'label' => 'Contact is (not) of Subtype(s)',
        'name' => 'contact_has_subtype',
      ),
      'CRM_CivirulesConditions_Contact_HasTag' => array(
        'label' => 'Contact Has/Does Not Have Tag(s)',
        'name' => 'contact_has_tag',
      ),
      'CRM_CivirulesConditions_Contact_InGroup' => array(
        'label' => 'Contact (not) in Group(s)',
        'name' => 'contact_in_group',
      ),
      'CRM_CivirulesConditions_Contact_InDomain' => array(
        'label' => 'Contact (not) in Domain',
        'name' => 'contact_in_domain',
      ),
      'CRM_CivirulesConditions_Contact_SubtypesChanged' => array(
        'label' => 'Contact Subtypes Changed',
        'name' => 'contact_sub_type_changed',
      ),
      'CRM_CivirulesConditions_Contact_LivesInCountry' => array(
        'label' => 'Contact Lives in Country',
        'name' => 'contact_in_country',
      ),
      'CRM_CivirulesConditions_Contribution_Amount' => array(
        'label' => 'Contribution Total Amount',
        'name' => 'contribution_total_amount',
      ),
      'CRM_CivirulesConditions_Contribution_Campaign' => array(
        'label' => 'Contribution is (not) in Campaign(s)',
        'name' => 'contribution_campaign',
      ),
      'CRM_CivirulesConditions_Contribution_DistinctContributingDay' => array(
        'label' => 'xth Day of Contributing by Donor',
        'name' => 'distinct_contributing_day_of_contact',
      ),
      'CRM_CivirulesConditions_Contribution_FinancialType' => array(
        'label' => 'Contribution is (not) of Financial Type(s)',
        'name' => 'contribution_financial_type',
      ),
      'CRM_CivirulesConditions_Contribution_FirstContribution' => array(
        'label' => 'First Contribution of a Contact',
        'name' => 'first_contribution_of_contact',
      ),
      'CRM_CivirulesConditions_Contribution_LastContribution' => array(
        'label' => 'Last Contribution of a Contact',
        'name' => 'last_contribution_of_contact',
      ),
      'CRM_CivirulesConditions_Contribution_PaidBy' => array(
        'label' => 'Contribution is (not) Paid by Method(s)',
        'name' => 'contribution_paid_by',
      ),
      'CRM_CivirulesConditions_Contribution_SpecificAmount' => array(
        'label' => 'xth Contribution of Amount xxx',
        'name' => 'contribution_specific_amount',
      ),
      'CRM_CivirulesConditions_Contribution_Status' => array(
        'label' => 'Contribution Status is',
        'name' => 'contribution_status',
      ),
      'CRM_CivirulesConditions_Contribution_StatusChanged' => array(
        'label' => 'Compare Old Contribution Status to New Contribution Status',
        'name' => 'contribution_status_changed',
      ),
      'CRM_CivirulesConditions_Contribution_TotalContributedAmount' => array(
        'label' => 'Total Contributed Amount',
        'name' => 'total_contributed_amount',
      ),
      'CRM_CivirulesConditions_ContributionRecur_Campaign' => array(
        'label' => 'Recurring Contribution is (not) in Campaign(s)',
        'name' => 'contribution_recur_campaign',
      ),
      'CRM_CivirulesConditions_ContributionRecur_Count' => array(
        'label' => 'xth Recurring Contribution Collection',
        'name' => 'contribution_recur_count',
      ),
      'CRM_CivirulesConditions_ContributionRecur_DonorIsRecurring' => array(
        'label' => 'Donor has Recurring Contribution',
        'name' => 'donor_has_recurring',
      ),
      'CRM_CivirulesConditions_ContributionRecur_EndDate' => array(
        'label' => 'End Date of Recurring Contribution',
        'name' => 'contribution_recur_end_date',
      ),
      'CRM_CivirulesConditions_Email_PrimaryEmailChanged' => array(
        'label' => 'Primary E-mail Address has Changed',
        'name' => 'primary_email_changed',
      ),
      'CRM_CivirulesConditions_EntityTag_TagId' => array(
        'label' => 'Tag is',
        'name' => 'entity_tag_tag_id',
      ),
      'CRM_CivirulesConditions_GroupContact_GroupId' => array(
        'label' => 'Group is',
        'name' => 'group_contact_group_id',
      ),
      'CRM_CivirulesConditions_Membership_ActiveMembership' => array(
        'label' => 'Contact has Active Membership of Type',
        'name' => 'active_membership_type',
      ),
      'CRM_CivirulesConditions_Membership_ContactHasMembership' => array(
        'label' => 'Contact has Membership of Status and Type',
        'name' => 'contact_has_membership',
      ),
      'CRM_CivirulesConditions_Membership_Status' => array(
        'label' => 'Membership Status is (not) one of',
        'name' => 'membership_status',
      ),
      'CRM_CivirulesConditions_Membership_Type' => array(
        'label' => 'Membership is (not) one of Type(s)',
        'name' => 'membership_type',
      ),
      'CRM_CivirulesConditions_Relationship_IsContactA' => array(
        'label' => 'Relationship is Contact A',
        'name' => 'relationship_is_contact_a',
      ),
      'CRM_CivirulesConditions_Relationship_IsContactB' => array(
        'label' => 'Relationship is Contact B',
        'name' => 'relationship_is_contact_b',
      ),
      'CRM_CivirulesConditions_Relationship_RelationshipType' => array(
        'label' => 'Relationship is (not) one of Type(s)',
        'name' => 'relationship_relationship_type',
      ),
      'CRM_CivirulesConditions_FieldValueComparison' => array(
        'label' => 'Field Value Comparison',
        'name' => 'field_value_comparison',
      ),
      'CRM_CivirulesConditions_Event_EventType' => array(
        'label' => 'Event is (not) of Type(s)',
        'name' => 'event_type',
      ),
      'CRM_CivirulesConditions_Participant_ParticipantRole' => array(
        'label' => 'Participant has (not) one of Role(s)',
        'name' => 'participant_role',
      ),
      'CRM_CivirulesConditions_Participant_ParticipantStatus' => array(
        'label' => 'Participant Status is (not) one of',
        'name' => 'participant_status',
      ),
      'CRM_CivirulesConditions_Activity_ActivityIsFuture' => array(
        'label' => 'Activity Date in the Future',
        'name' => 'activity_is_future_date',
      ),
      'CRM_CivirulesConditions_Activity_ActivityIsPast' => array(
        'label' => 'Activity Date in the Past',
        'name' => 'activity_is_past_date',
      ),
    );
    foreach ($conditions as $className => $conditionData) {
      $select = "SELECT COUNT(*) FROM civirule_condition WHERE class_name = %1";
      $count = CRM_Core_DAO::singleValueQuery($select, array(1 => array($className, 'String')));
      if ($count == 0) {
        $insert = "INSERT INTO civirule_condition (name, label, class_name, is_active) VALUES(%1, %2, %3, %4)";
        CRM_Core_DAO::executeQuery($insert, array(
          1 => array($conditionData['name'], 'String'),
          2 => array($conditionData['label'], 'String'),
          3 => array($className, 'String'),
          4 => array(1, 'Integer'),
        ));
      }
    }
  }

  /**
   * Read a json file and insert the action into the database.
   *
   * The file has the format of:
   * [
   *   {
   *     "name": "activity_add",
   *     "label": "Add activity to contact",
   *     "class_name": "CRM_CivirulesActions_Activity_Add",
   *   }
   * ]
   *
   * @param string $jsonFile
   *
   * @throws \Exception
   */
  public static function insertActionsFromJson($jsonFile) {
    $actions = json_decode(file_get_contents($jsonFile), true);
    foreach($actions as $action) {
      self::insertAction($action['name'], $action['label'], $action['class_name']);
    }
  }

  /**
   * Insert an action in the database and check whether the action already exists.
   *
   * @param $name
   * @param $label
   * @param $className
   */
  public static function insertAction($name, $label, $className) {
    $user_id = CRM_Core_Session::getLoggedInContactID();
    if (!$user_id) {
      $user_id = 0;
    }
    $id = CRM_Core_DAO::singleValueQuery("SELECT `id` FROM `civirule_action` WHERE `name` = %1", array(1=>array($name, 'String')));
    if ($id) {
      $update = "UPDATE `civirule_action` SET `label` = %2, `class_name` = %3, `modified_date` = NOW(), `modified_user_id` = %4 WHERE id = %1";
      $updateParams[1] = array($id, 'Integer');
      $updateParams[2] = array($label, 'String');
      $updateParams[3] = array($className, 'String');
      $updateParams[4] = array($user_id, 'Integer');
      CRM_Core_DAO::executeQuery($update, $updateParams);
    } else {
      $insert = "INSERT INTO `civirule_action` (`name`, `label`, `class_name`, `created_date`, `created_user_id`) VALUES (%1, %2, %3, NOW(), %4)";
      $insertParams[1] = array($name, 'String');
      $insertParams[2] = array($label, 'String');
      $insertParams[3] = array($className, 'String');
      $insertParams[4] = array($user_id, 'Integer');
      CRM_Core_DAO::executeQuery($insert, $insertParams);
    }
  }

  /**
   * Read a json file and insert the conditions into the database.
   *
   * The file has the format of:
   * [
   *   {
   *     "name": "activity_in_campaign",
   *     "label": "Activity is (not) in Campaign(s)",
   *     "class_name": "CRM_CivirulesConditions_Activity_Campaign",
   *   }
   * ]
   *
   * @param string $jsonFile
   *
   * @throws \Exception
   */
  public static function insertConditionsFromJson($jsonFile) {
    $conditions = json_decode(file_get_contents($jsonFile), true);
    foreach($conditions as $condition) {
      self::insertCondition($condition['name'], $condition['label'], $condition['class_name']);
    }
  }

  /**
   * Insert an condition in the database and check whether the action already exists.
   *
   * @param $name
   * @param $label
   * @param $className
   */
  public static function insertCondition($name, $label, $className) {
    $user_id = CRM_Core_Session::getLoggedInContactID();
    if (!$user_id) {
      $user_id = 0;
    }
    $id = CRM_Core_DAO::singleValueQuery("SELECT `id` FROM `civirule_condition` WHERE `name` = %1", array(1=>array($name, 'String')));
    if ($id) {
      $update = "UPDATE `civirule_condition` SET `label` = %2, `class_name` = %3, `modified_date` = NOW(), `modified_user_id` = %4 WHERE id = %1";
      $updateParams[1] = array($id, 'Integer');
      $updateParams[2] = array($label, 'String');
      $updateParams[3] = array($className, 'String');
      $updateParams[4] = array($user_id, 'Integer');
      CRM_Core_DAO::executeQuery($update, $updateParams);
    } else {
      $insert = "INSERT INTO `civirule_condition` (`name`, `label`, `class_name`, `created_date`, `created_user_id`) VALUES (%1, %2, %3, NOW(), %4)";
      $insertParams[1] = array($name, 'String');
      $insertParams[2] = array($label, 'String');
      $insertParams[3] = array($className, 'String');
      $insertParams[4] = array($user_id, 'Integer');
      CRM_Core_DAO::executeQuery($insert, $insertParams);
    }
  }

  /**
   * Read a json file and insert tre triggers to the database.
   *
   * The file has the format of:
   * [
   *   {
   *     "name": "new_activity",
   *     "label": "Activity is added",
   *     "object_name": "Activity",
   *     "op": "create",
   *     "class_name": "CRM_CivirulesPostTrigger_Activity",
   *     "cron": 0
   *   }
   * ]
   *
   * @param string $jsonFile
   *
   * @throws \Exception
   */
  public static function insertTriggersFromJson($jsonFile) {
    $triggers = json_decode(file_get_contents($jsonFile), true);
    foreach($triggers as $trigger) {
      if (!isset($trigger['object_name'])) {
        $trigger['object_name'] = null;
      }
      if (!isset($trigger['op'])) {
        $trigger['op'] = null;
      }
      if (!isset($trigger['class_name'])) {
        $trigger['class_name'] = null;
      }
      self::insertTrigger($trigger['name'], $trigger['label'], $trigger['cron'], $trigger['class_name'], $trigger['object_name'], $trigger['op']);
    }
  }

  /**
   * Insert a trigger. Checks if trigger already exists and if so update the existing one.
   *
   * @param $name
   * @param $label
   * @param $cron
   * @param null $className
   * @param null $objectName
   * @param null $op
   *
   * @throws \Exception
   */
  public static function insertTrigger($name, $label, $cron, $className=null, $objectName = null, $op = null) {
    if (!empty($objectName) && empty($op)) {
      throw new \Exception('Op parameter could not be empty for trigger '.$name);
    }
    if (empty($objectName) && !empty($op)) {
      throw new \Exception('Object Name parameter could not be empty for trigger '.$name);
    }
    if (empty($objectName) && empty($op) && empty($className)) {
      throw new \Exception('Object Name, op and class Name parameter could not be empty for trigger '.$name);
    }
    $user_id = CRM_Core_Session::getLoggedInContactID();
    if (!$user_id) {
      $user_id = 0;
    }
    $id = CRM_Core_DAO::singleValueQuery("SELECT `id` FROM `civirule_trigger` WHERE `name` = %1", array(1=>array($name, 'String')));
    if ($id) {
      $update = "UPDATE `civirule_trigger` SET `label` = %2, `cron` = %3, `modified_date` = NOW(), `modified_user_id` = %4";
      $updateParams[1] = array($id, 'Integer');
      $updateParams[2] = array($label, 'String');
      $updateParams[3] = array($cron ? '1': '0', 'Boolean');
      $updateParams[4] = array($user_id, 'Integer');
      $i = 5;
      if ($objectName && $op) {
        $update .= ", `object_name` = %{$i}";
        $updateParams[$i] = array($objectName, 'String');
        $i++;
        $update .= ", `op` = %{$i}";
        $updateParams[$i] = array($op, 'String');
        $i++;
      } else {
        $update .= ", `object_name` = null, `op` = null";
      }
      if ($className) {
        $update .= ", `class_name` = %{$i}";
        $updateParams[$i] = array($className, 'String');
        $i++;
      } else {
        $update .= ", `class_name` = null";
      }
      $update .= " WHERE id = %1";
      CRM_Core_DAO::executeQuery($update, $updateParams);
    } else {
      $insert = "INSERT INTO `civirule_trigger` (`name`, `label`, `cron`, `created_date`, `created_user_id`";
      $insertParams[1] = array($name, 'String');
      $insertParams[2] = array($label, 'String');
      $insertParams[3] = array($cron ? '1': '0', 'Boolean');
      $insertParams[4] = array($user_id, 'Integer');
      $valueStatement = "(%1, %2, %3, NOW(), %4";
      $i = 5;
      if ($objectName && $op) {
        $insert .= ", `object_name`";
        $valueStatement .= ", %{$i}";
        $insertParams[$i] = array($objectName, 'String');
        $i++;
        $insert .= ", `op`";
        $valueStatement .= ", %{$i}";
        $insertParams[$i] = array($op, 'String');
        $i++;
      }
      if ($className) {
        $insert .= ", `class_name`";
        $valueStatement .= ", %{$i}";
        $insertParams[$i] = array($className, 'String');
        $i++;
      }
      $insert .= ") VALUES ".$valueStatement.");";
      CRM_Core_DAO::executeQuery($insert, $insertParams);
    }
  }

  /**
   * Method to check for triggers and insert if required
   *
   * @deprecated because the triggers are not put in a json file and
   * every trigger is checked for existence when the json file is processed
   */
  public static function checkCiviRulesTriggers() {
    $triggers = array(
      'CRM_CivirulesPostTrigger_ContactCustomDataChanged' => array(
        'label' => 'Custom Data on Contact (of any Type) Changed',
        'name' => 'changed_contact_custom_data',
      ),
      'CRM_CivirulesPostTrigger_HouseholdCustomDataChanged' => array(
        'label' => 'Custom Data on Household Changed',
        'name' => 'changed_household_custom_data',
      ),
      'CRM_CivirulesPostTrigger_IndividualCustomDataChanged' => array(
        'label' => 'Custom Data on Individual Changed',
        'name' => 'changed_individual_custom_data',
      ),
      'CRM_CivirulesPostTrigger_OrganizationCustomDataChanged' => array(
        'label' => 'Custom Data on Organization Changed',
        'name' => 'changed_organization_custom_data',
      ),
    );
    foreach ($triggers as $className => $triggerData) {
      $select = "SELECT COUNT(*) FROM civirule_trigger WHERE class_name = %1";
      $count = CRM_Core_DAO::singleValueQuery($select, array(1 => array($className, 'String')));
      if ($count == 0) {
        $insert = "INSERT INTO civirule_trigger (name, label, cron, class_name, is_active) VALUES(%1, %2, %3, %4, %5)";
        CRM_Core_DAO::executeQuery($insert, array(
          1 => array($triggerData['name'], 'String'),
          2 => array($triggerData['label'], 'String'),
          3 => array(0, 'Integer'),
          4 => array($className, 'String'),
          5 => array(1, 'Integer'),
        ));
      }
    }
  }
}
