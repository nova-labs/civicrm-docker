<?php

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesPostTrigger_Activity extends CRM_Civirules_Trigger_Post {

  public function setTriggerParams($triggerParams) {
    $this->triggerParams = unserialize($triggerParams);
  }

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'Activity');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Activity_DAO_Activity';
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
    $triggerData = $this->getTriggerDataFromPost($op, $objectName, $objectId, $objectRef);
    //trigger for activity trigger for every source_contact_id, target_contact_id and assignee_contact_id
    $activityContact = new CRM_Activity_BAO_ActivityContact();
    $activityContact->activity_id = $objectId;
    if ($this->triggerParams && isset($this->triggerParams['record_type']) && $this->triggerParams['record_type']) {
      $activityContact->record_type_id = $this->triggerParams['record_type'];
    }
    $activityContact->find();
    while($activityContact->fetch()) {
      $data = array();
      CRM_Core_DAO::storeValues($activityContact, $data);
      $triggerData->setEntityData('ActivityContact', $data);
      if (isset($data['contact_id']) && $data['contact_id']) {
        $triggerData->setContactId($data['contact_id']);
      }
      CRM_Civirules_Engine::triggerRule($this, clone $triggerData);
    }
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('ActivityContact', 'ActivityContact', 'CRM_Activity_DAO_ActivityContact' , 'ActivityContact');
    return $entities;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a trigger
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleId) {
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/activity', 'rule_id='.$ruleId);
  }

  /**
   * Returns a description of this trigger
   *
   * @return string
   * @access public
   * @abstract
   */
  public function getTriggerDescription() {
    $result = civicrm_api3('ActivityContact', 'getoptions', [
      'field' => "record_type_id",
    ]);
    $options[0] = E::ts('For all contacts');
    $options = array_merge($options, $result['values']);
    return E::ts('Trigger for %1', array(1=>$options[$this->triggerParams['record_type']]));
  }

}
