<?php

/**
 * Trigger when an Activity linked to a Case changes.
 *
 * Notice: this class extends from CRM_CivirulesPostTrigger_Activity
 * (trigger on Activity change). By doing this, we reuse all the
 * Activity triggering logic, while still filtering for Case-related
 * activities.
 */
class CRM_CivirulesPostTrigger_CaseActivity extends CRM_CivirulesPostTrigger_Activity {

  /**
   * Override getTriggerDataFromPost() so that we can append the Case
   * entity to the trigger data.
   */
  protected function getTriggerDataFromPost($op, $objectName, $objectId, $objectRef) {
    $triggerData = parent::getTriggerDataFromPost($op, $objectName,
                                                  $objectId, $objectRef);

    $case = new CRM_Case_BAO_Case();
    if ($objectRef instanceof CRM_Activity_DAO_Activity && $objectRef->case_id) {
      $case->id = $objectRef->case_id;
    } else {
      // Get the CaseActivity record.
      $caseActivity = new CRM_Case_DAO_CaseActivity();
      $caseActivity->activity_id = $objectId;
      if ($caseActivity->find(TRUE)) {
        // Now load the case.
        $case->id = $caseActivity->case_id;
      }
    }

    if ($case->id && $case->find(TRUE)) {
      $data = array();
      CRM_Core_DAO::storeValues($case, $data);
      $triggerData->setEntityData('Case', $data);
    }
    return $triggerData;
  }


  /**
   * Trigger a rule for this trigger
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef) {
    if ($this->isCaseActivity($op, $objectName, $objectId, $objectRef)) {
      // It is a Case-related activity -- let our parent trigger
      // actually trigger the rule.
      parent::triggerTrigger($op, $objectName, $objectId, $objectRef);
    }
  }

  protected function isCaseActivity($op, $objectName, $objectId, $objectRef) {
    if ($objectName != 'Activity') {
      return false;
    }
    if (isset($objectRef->case_id) && !empty($objectRef->case_id)) {
      return true;
    } elseif (CRM_Case_BAO_Case::isCaseActivity($objectId)) {
      return true;
    }
    return false;
  }


  /**
   * Returns additional entities provided in this trigger.
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Case', 'Case', 'CRM_Case_DAO_Case' , 'Case');
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('CaseActivity', 'CaseActivity', 'CRM_Case_DAO_CaseActivity' , 'CaseActivity');
    return $entities;
  }
}
