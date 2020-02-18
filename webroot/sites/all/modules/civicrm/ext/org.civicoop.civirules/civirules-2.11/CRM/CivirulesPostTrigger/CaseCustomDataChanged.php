<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
/**
 * Trigger when an Activity linked to a Case changes.
 *
 * Notice: this class extends from CRM_CivirulesPostTrigger_Activity
 * (trigger on Activity change). By doing this, we reuse all the
 * Activity triggering logic, while still filtering for Case-related
 * activities.
 */
class CRM_CivirulesPostTrigger_CaseCustomDataChanged extends CRM_Civirules_Trigger {

  private static $preData = false;

  private static $triggers = false;

  private static function getTriggers() {
    if (!self::$triggers) {
      self::$triggers = CRM_Civirules_BAO_Rule::findRulesByClassname('CRM_CivirulesPostTrigger_CaseCustomDataChanged');
    }
    return self::$triggers;
  }

  public function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition('Case', 'Case', $this->getDaoClassName(), 'Case');
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Relationship', 'Relationship', 'CRM_Contact_DAO_Relationship' , 'Relationship');
    return $entities;
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Case_DAO_Case';
  }

  public static function custom($op, $groupID, $entityID, &$params) {
    $custom_group = civicrm_api3('CustomGroup', 'getsingle', array('id' => $groupID));
    if ($custom_group['extends'] != 'Case') {
      return;
    }
    $case = civicrm_api3('Case', 'getsingle', array('id' => $entityID));
    foreach($params as $field) {
      if (!empty($field['custom_field_id'])) {
        $case['custom_' . $field['custom_field_id']] = $field['value'];
      }
    }
    if (self::$preData !== false) {
      $t = new CRM_Civirules_TriggerData_Edit('Case', $entityID, $case, self::$preData);
    } else {
      $t = new CRM_Civirules_TriggerData_Post('Case', $entityID, $case);
    }

    //trigger for each client
    $clients = CRM_Case_BAO_Case::getCaseClients($entityID);
    foreach($clients as $client) {
      $triggerData = clone $t;
      $triggerData->setEntityData('Relationship', null);
      $triggerData->setContactId((int) $client);
      self::trigger($triggerData);
    }

    //trigger for each case role
    $relatedContacts = CRM_Case_BAO_Case::getRelatedContacts($entityID);
    foreach($relatedContacts as $contact) {
      $triggerData = clone $t;
      $relationshipData = null;
      $relationship = new CRM_Contact_BAO_Relationship();
      $relationship->contact_id_b = $contact['contact_id'];
      $relationship->case_id = $entityID;
      if ($relationship->find(true)) {
        CRM_Core_DAO::storeValues($relationship, $relationshipData);
      }
      $triggerData->setEntityData('Relationship', $relationshipData);
      $triggerData->setContactId($contact['contact_id']);
      self::trigger($triggerData);
    }
  }

  protected static function trigger(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    //find matching rules for this objectName and op
    $triggers = self::getTriggers();
    foreach($triggers as $trigger) {
      CRM_Civirules_Engine::triggerRule($trigger, $triggerData);
    }
  }

  public static function validateForm($form) {
    if ($form instanceof CRM_Case_Form_CustomData) {
      $defaults = $form->getVar('_defaultValues');
      self::$preData = array();
      foreach($defaults as $key => $value) {
        list($_custom, $field_id, $rec_id) = explode("_", $key);
        self::$preData['custom_'.$field_id] = $value;
      }
    }
  }

}