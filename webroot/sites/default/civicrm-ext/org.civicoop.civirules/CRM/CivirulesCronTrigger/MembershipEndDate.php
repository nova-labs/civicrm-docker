<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesCronTrigger_MembershipEndDate extends CRM_Civirules_Trigger_Cron {

  private $dao = false;

  public static function intervals() {
    return array(
      '-days' => ts('Day(s) before end date'),
      '-weeks' => ts('Week(s) before end date'),
      '-months' => ts('Month(s) before end date'),
      '+days' => ts('Day(s) after end date'),
      '+weeks' => ts('Week(s) after end date'),
      '+months' => ts('Month(s) after end date'),
    );
  }

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
      $triggerData = new CRM_Civirules_TriggerData_Cron($this->dao->contact_id, 'Membership', $data);
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
    return new CRM_Civirules_TriggerData_EntityDefinition(ts('Membership'), 'Membership', 'CRM_Member_DAO_Membership', 'Membership');
  }

  /**
   * Method to query trigger entities
   *
   * @access private
   */
  private function queryForTriggerEntities() {
    if (empty($this->triggerParams['membership_type_id'])) {
      return false;
    }

    $params[1] = array($this->triggerParams['membership_type_id'], 'Integer');
    $end_date_statement = "AND DATE(m.end_date) = CURRENT_DATE()";
    switch ($this->triggerParams['interval_unit']) {
      case '-days':
        $end_date_statement = "AND DATE_SUB(m.end_date, INTERVAL %2 DAY) = CURRENT_DATE()";
        $params[2] = array($this->triggerParams['interval'], 'Integer');
        break;
      case '-weeks':
        $end_date_statement = "AND DATE_SUB(m.end_date, INTERVAL %2 WEEK) = CURRENT_DATE()";
        $params[2] = array($this->triggerParams['interval'], 'Integer');
        break;
      case '-months':
        $end_date_statement = "AND DATE_SUB(m.end_date, INTERVAL %2 MONTH) = CURRENT_DATE()";
        $params[2] = array($this->triggerParams['interval'], 'Integer');
        break;
      case '+days':
        $end_date_statement = "AND DATE_ADD(m.end_date, INTERVAL %2 DAY) = CURRENT_DATE()";
        $params[2] = array($this->triggerParams['interval'], 'Integer');
        break;
      case '+weeks':
        $end_date_statement = "AND DATE_ADD(m.end_date, INTERVAL %2 WEEK) = CURRENT_DATE()";
        $params[2] = array($this->triggerParams['interval'], 'Integer');
        break;
      case '+months':
        $end_date_statement = "AND DATE_ADD(m.end_date, INTERVAL %2 MONTH) = CURRENT_DATE()";
        $params[2] = array($this->triggerParams['interval'], 'Integer');
        break;
    }

    $sql = "SELECT m.*
            FROM `civicrm_membership` `m`
            WHERE `m`.`membership_type_id` = %1
            {$end_date_statement} 
            AND `m`.`contact_id` NOT IN (
              SELECT `rule_log`.`contact_id`
              FROM `civirule_rule_log` `rule_log`
              WHERE `rule_log`.`rule_id` = %3 AND DATE(`rule_log`.`log_date`) = DATE(NOW())
            )";
    $params[3] = array($this->ruleId, 'Integer');
    $this->dao = CRM_Core_DAO::executeQuery($sql, $params, true, 'CRM_Member_DAO_Membership');

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
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/membershipenddate/', 'rule_id='.$ruleId);
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
    $membership_types = CRM_Civirules_Utils::getMembershipTypes();
    $interval_units = self::intervals();


    $membershipTypeLabel = $membership_types[$this->triggerParams['membership_type_id']];
    $intervalUnitLabel = $interval_units[$this->triggerParams['interval_unit']];

    return ts('Membership end date with type %1 %2 %3', array(1 => $membershipTypeLabel, 2=> $this->triggerParams['interval'], $intervalUnitLabel));
  }

}