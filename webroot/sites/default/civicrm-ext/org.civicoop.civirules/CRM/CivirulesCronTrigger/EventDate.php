<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesCronTrigger_EventDate extends CRM_Civirules_Trigger_Cron {

  private $dao = false;

  /**
   * This function returns a CRM_Civirules_TriggerData_TriggerData this entity is used for triggering the rule
   *
   * Return false when no next entity is available
   *
   * @return CRM_Civirules_TriggerData_TriggerData|false
   */
  protected function getNextEntityTriggerData() {
    static $_eventCache = array();
    if (!$this->dao) {
      if (!$this->queryForTriggerEntities()) {
        return false;
      }
    }
    if ($this->dao->fetch()) {
      $participant = array();
      CRM_Core_DAO::storeValues($this->dao, $participant);
      $triggerData = new CRM_Civirules_TriggerData_Cron($this->dao->contact_id, 'Participant', $participant);
      if (!isset($_eventCache[$participant['event_id']])) {
        $_eventCache[$participant['event_id']] = civicrm_api3('Event', 'getsingle', ['id' => $participant['event_id']]);
      }
      $triggerData->setEntityData('Event', $_eventCache[$participant['event_id']]);
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
    return new CRM_Civirules_TriggerData_EntityDefinition('Participant', 'Participant', 'CRM_Event_DAO_Participant', 'Participant');
  }

  /**
   * Method to query trigger entities
   *
   * @access private
   */
  private function queryForTriggerEntities() {
    if (empty($this->triggerParams['date_field'])) {
      return false;
    }

    $dateField = $this->triggerParams['date_field'];
    if (!empty($this->triggerParams['offset'])) {
      $unit = 'DAY';
      if (!empty($this->triggerParams['offset_unit'])) {
        $unit = $this->triggerParams['offset_unit'];
      }
      $offset = CRM_Utils_Type::escape($this->triggerParams['offset'], 'Integer');
      if ($this->triggerParams['offset_type'] == '-') {
        $dateExpression = "DATE_SUB(`e`.`".$dateField."`, INTERVAL ".$offset." ".$unit .")";
      } else {
        $dateExpression = "DATE_ADD(`e`.`".$dateField."`, INTERVAL ".$offset." ".$unit .")";
      }
    } else {
      $dateExpression = "DATE(`e`.`".$dateField."`)";
    }

    $sql = "SELECT `p`.*
            FROM `civicrm_participant` `p`
            INNER JOIN `civicrm_event` `e` ON `e`.`id` = `p`.`event_id`
            WHERE {$dateExpression} = CURDATE() 
            AND `e`.`event_type_id` = %1
            AND `p`.`contact_id` NOT IN (
              SELECT `rule_log`.`contact_id`
              FROM `civirule_rule_log` `rule_log`
              WHERE `rule_log`.`rule_id` = %2 AND DATE(`rule_log`.`log_date`) = DATE(NOW())
            )";
    $params[1] = array($this->triggerParams['event_type_id'], 'Integer');
    $params[2] = array($this->ruleId, 'Integer');
    $this->dao = CRM_Core_DAO::executeQuery($sql, $params, true, 'CRM_Event_DAO_Participant');

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
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/eventdate/', 'rule_id='.$ruleId);
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
    $fields = array(
      'start_date' => ts('Start Date'),
      'end_date' => ts('End Date'),
    );
    $eventTypeLabel = CRM_Civirules_Utils::getOptionLabelWithValue(CRM_Civirules_Utils::getOptionGroupIdWithName('event_type'),  $this->triggerParams['event_type_id']);
    $fieldLabel = $fields[$this->triggerParams['date_field']];
    $offsetLabel = '';
    if (!empty($this->triggerParams['offset'])) {
      $offsetTypes = array(
        '-' => ts('before'),
        '+' => ts('after'),
      );
      $offsetUnits = array(
        'DAY' => ts('Day(s)'),
        'WEEK' => ts('Week(s)'),
        'MONTH' => ts('Month(s)'),
        'YEAR' => ts('Year(s)')
      );
      $offsetLabel = $offsetTypes[$this->triggerParams['offset_type']].' '.$this->triggerParams['offset'].' '.$offsetUnits[$this->triggerParams['offset_unit']];
    }

    return ts('Event with type %1 and field %2 date reached %3', array(1 => $eventTypeLabel, 2=> $fieldLabel, 3 => $offsetLabel));
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Event', 'Event', 'CRM_Event_DAO_Event' , 'Event');
    return $entities;
  }

}