<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesPostTrigger_Relationship extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'Relationship');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Contact_DAO_Relationship';
  }

  /**
   * Inherited from parent to add case to the triggerData when a relationship does contain case_id.
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   * @return CRM_Civirules_TriggerData_Edit|CRM_Civirules_TriggerData_Post
   */
  protected function getTriggerDataFromPost($op, $objectName, $objectId, $objectRef) {
    $triggerData = parent::getTriggerDataFromPost($op, $objectName, $objectId, $objectRef);
    $relationship = $triggerData->getEntityData('Relationship');
    if (!empty($relationship['case_id'])) {
      try {
        $case = civicrm_api3('Case', 'getsingle', array('id' => $relationship['case_id']));
        $triggerData->setEntityData('Case', $case);
      } catch (Exception $e) {
        // Do nothing.
      }
    }
    return $triggerData;
  }

  /**
   * Returns additional entities provided in this trigger.
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Case', 'Case', 'CRM_Case_DAO_Case' , 'Case');
    return $entities;
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
    $t = $this->getTriggerDataFromPost($op, $objectName, $objectId, $objectRef);
    $relationship = $t->getEntityData('Relationship');
    if (!empty($relationship['contact_id_a'])) {
      $triggerData = clone $t;
      $triggerData->setContactId($relationship['contact_id_a']);
      CRM_Civirules_Engine::triggerRule($this, $triggerData);
    }
    if (!empty($relationship['contact_id_b'])) {
      $triggerData = clone $t;
      $triggerData->setContactId($relationship['contact_id_b']);
      CRM_Civirules_Engine::triggerRule($this, $triggerData);
    }
  }

}