<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

/**
 * Class CRM_CivirulesPostTrigger_Participant
 *
 * Use a custom class for event participant triggers because
 * we will add the event data to the triggerData as soon as the participant trigger
 * is triggered.
 *
 */
class CRM_CivirulesPostTrigger_Participant extends CRM_Civirules_Trigger_Post {

  protected function getTriggerDataFromPost($op, $objectName, $objectId, $objectRef) {
    $triggerData = parent::getTriggerDataFromPost($op, $objectName, $objectId, $objectRef);
    $participant = $triggerData->getEntityData('Participant');
    $event = civicrm_api3('Event', 'getsingle', array('id' => $participant['event_id']));
    $triggerData->setEntityData('Event', $event);
    return $triggerData;
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Event', 'Event', 'CRM_Event_DAO_Event', 'Event');
    return $entities;
  }

}