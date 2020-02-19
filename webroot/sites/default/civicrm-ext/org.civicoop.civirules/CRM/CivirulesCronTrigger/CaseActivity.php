<?php

class CRM_CivirulesCronTrigger_CaseActivity extends CRM_Civirules_Trigger_Cron {

  private $dao = false;

  /**
   * This function returns a CRM_Civirules_TriggerData_TriggerData this entity is used for triggering the rule
   *
   * Return false when no next entity is available
   *
   * @return CRM_Civirules_TriggerData_TriggerData|false
   */
  protected function getNextEntityTriggerData() {
    if (!$this->dao) {
      if (!$this->queryForTriggerEntities()) {
        return false;
      }
    }
    if ($this->dao->fetch()) {
      $data = array();
      CRM_Core_DAO::storeValues($this->dao, $data);
      $triggerData = new CRM_Civirules_TriggerData_Cron(0, 'Case', $data);
      return $triggerData;
    }
    return false;
  }

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition('Case', 'Case', 'CRM_Case_DAO_Case', 'Cases');
  }

  /**
   * Method to query trigger entities
   *
   * @access private
   */
  private function queryForTriggerEntities() {

    $sql = "SELECT c.*
            FROM `civicrm_case` `c`
            WHERE c.status_id = 1
            ";
    $this->dao = CRM_Core_DAO::executeQuery($sql, array(), true, 'CRM_Case_DAO_Case');

    return true;
  }
}
