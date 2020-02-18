<?php
/**
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Aug 2018
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesCronTrigger_HasTag extends CRM_Civirules_Trigger_Cron {

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
      $triggerData = new CRM_Civirules_TriggerData_Cron($this->dao->contact_id, 'EntityTag', $data);
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
    return new CRM_Civirules_TriggerData_EntityDefinition('EntityTag', 'EntityTag', 'CRM_Core_DAO_EntityTag', 'EntityTag');
  }

  /**
   * Method to query trigger entities
   *
   * @access private
   */
  private function queryForTriggerEntities() {

    if (empty($this->triggerParams['tag_id'])) {
      return false;
    }


    if (is_array($this->triggerParams['group_id'])) {
      $this->triggerParams['tag_id'] = CRM_Utils_Type::escapeAll($this->triggerParams['tag_id'], 'Integer');
      $tagWhereStatement = "`c`.`group_id` IN (".implode(", ", $this->triggerParams['group_id']).")";
    } else {
      $groupWhereStatement = "`c`.`group_id` = '".CRM_Utils_Type::escape($this->triggerParams['group_id'], 'Integer', true)."'";
    }

    $sql = "SELECT c.*
            FROM `civicrm_group_contact` `c`
            WHERE {$groupWhereStatement} AND c.status = 'Added'
            AND `c`.`contact_id` NOT IN (
              SELECT `rule_log`.`contact_id`
              FROM `civirule_rule_log` `rule_log`
              WHERE `rule_log`.`rule_id` = %1 AND DATE(`rule_log`.`log_date`) = DATE(NOW())
            )";

    $params[1] = array($this->ruleId, 'Integer');
    $this->dao = CRM_Core_DAO::executeQuery($sql, $params, true, 'CRM_Contact_DAO_GroupContact');

    return true;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleId) {
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/hastag/', 'rule_id='.$ruleId);
  }

  public function setTriggerParams($triggerParams) {
    $this->triggerParams = unserialize($triggerParams);
  }

  /**
   * Returns a description of this trigger
   *
   * @return string
   * @access public
   * @abstract
   */
  public function getTriggerDescription() {
    $groupName = ts('Unknown');
    if (is_array($this->triggerParams['group_id'])) {
      $groupApi = civicrm_api3('Group', 'get', array('id' => array('IN' => $this->triggerParams['group_id']), 'options' => array('limit' => 0)));
      $groupNames = array();
      foreach($groupApi['values'] as $group) {
        $groupNames[] = $group['title'];
      }
      if (!empty($groupNames)) {
        $groupName = implode(", ", $groupNames);
      }
    } else {
      try {
        $groupName = civicrm_api3('Group', 'getvalue', [
          'return' => 'title',
          'id' => $this->triggerParams['group_id']
        ]);
      } catch (Exception $e) {
        //do nothing
      }
    }
    return ts('Daily trigger for all members of group %1', array(
      1 => $groupName
    ));
  }
}