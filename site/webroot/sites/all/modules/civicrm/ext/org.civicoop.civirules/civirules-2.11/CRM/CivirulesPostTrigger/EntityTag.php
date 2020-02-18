<?php

class CRM_CivirulesPostTrigger_EntityTag extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'EntityTag');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Core_DAO_EntityTag';
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

    $entity = CRM_Civirules_Utils_ObjectName::convertToEntity($objectName);

    //only execute entity tag for setting or removing tags from contacts
    //because we need to know the contact id for the trigger engine
    //and we only know this when the tag is on contact level
    if (!isset($objectRef['1']) || $objectRef['1'] != 'civicrm_contact') {
      return;
    }

    foreach($objectRef['0'] as $cid) {
      $data = array (
        'tag_id' => $objectId,
        'entity_id' => $cid,
        'entity_table' => $objectRef['1'],
        'contact_id' => $cid,
      );
      $triggerData = new CRM_Civirules_TriggerData_Post($entity, $objectId, $data);
      CRM_Civirules_Engine::triggerRule($this, $triggerData);
    }
  }

}