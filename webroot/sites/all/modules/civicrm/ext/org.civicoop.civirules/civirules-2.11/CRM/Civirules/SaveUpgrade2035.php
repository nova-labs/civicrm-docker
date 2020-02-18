<?php
use CRM_Civirules_ExtensionUtil as E;

/**
 * Class to create save tables and save rules before executing upgrade 2035
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Sep 2019
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Civirules_SaveUpgrade2035 {
  private $_triggerId = NULL;

  /**
   * CRM_Civirules_SaveUpgrade2035 constructor.
   *
   * @param $triggerId
   * @throws Exception
   */
  public function __construct($triggerId) {
    if (empty($triggerId)) {
      throw new Exception(E::ts('Can not process empty trigger id in ') . __METHOD__);
    }
    $this->_triggerId = (int) $triggerId;
  }

  /**
   * Method to save rules on case added trigger prior to completing upgrade 2035
   */
  public function saveOldRecords() {
    // first check if required tables exist
    $tablesExist = TRUE;
    $requiredTables = ['civirule_pre210_rule', 'civirule_pre210_rule_action', 'civirule_pre210_rule_condition'];
    foreach ($requiredTables as $requiredTable) {
      if (!CRM_Core_DAO::checkTableExists($requiredTable)) {
        Civi::log()->error(E::ts('Could not find required table ') . $requiredTable . ", triggers on Case Added not saved but written to log!");
        $tablesExist = FALSE;
      }
      else {
        CRM_Core_DAO::executeQuery("TRUNCATE TABLE " . $requiredTable);
      }
    }
    if ($tablesExist) {
      Civi::log()->warning(E::ts('Rules using trigger Case is added will be deleted in the Civirules upgrade to 2.10. They will be saved in the temporary tables civirule_pre210_rule/rule_action/rule_condition. Use that data to re-enter them manually using trigger Case Activity added with condition activity type is Open Case'));
    }
    // get all rules with trigger and copy or write to log
    $rule = CRM_Core_DAO::executeQuery("SELECT * FROM civirule_rule WHERE trigger_id = %1", [1 => [$this->_triggerId, "Integer"]]);
    while ($rule->fetch()) {
      $this->saveRuleRule($rule, $tablesExist);
      // next get all related actions and triggers and save those too
      $action = CRM_Core_DAO::executeQuery("SELECT * FROM civirule_rule_action WHERE rule_id = %1", [1 =>[$rule->id, "Integer"]]);
      while ($action->fetch()) {
        $this->saveRuleAction($action, $tablesExist);
      }
      $condition = CRM_Core_DAO::executeQuery("SELECT * FROM civirule_rule_condition WHERE rule_id = %1", [1 =>[$rule->id, "Integer"]]);
      while ($condition->fetch()) {
        $this->saveRuleCondition($condition, $tablesExist);
      }
    }
  }

  /**
   * Method to save the rule rule data to either pre210 table or write in log
   *
   * @param $rule
   * @param $tablesExist
   */
  private function saveRuleRule($rule, $tablesExist) {
    if ($tablesExist) {
      $columns = ['name', 'is_active'];
      $values = ['%1', '%2'];
      $queryParams = [
        1 => [$rule->name, "String"],
        2 => [$rule->is_active, "Integer"],
      ];
      $index = 2;
      $possibles = ['label', 'trigger_params', 'description', 'help_text'];
      foreach ($possibles as $possible) {
        if ($rule->$possible) {
          $index++;
          $columns[] = $possible;
          $values[] = "%" . $index;
          $queryParams[$index] = [$rule->$possible, "String"];
        }
      }
      $insert = "INSERT INTO civirule_pre210_rule (" . implode(',', $columns) . ") VALUES(" . implode(',', $values). ")";
      CRM_Core_DAO::executeQuery($insert, $queryParams);
    }
    else {
      $ruleArray = CRM_Civirules_Utils::moveDaoToArray($rule);
      Civi::log()->warning(E::ts('The rule below will be deleted during the Civirules upgrade to release 2.10:'));
      Civi::log()->warning(E::ts('Data from table civirule_rule: ') . json_encode($ruleArray));
    }
  }

  /**
   * Method to save the rule action data to either pre210 table or write in log
   *
   * @param $action
   * @param $tablesExist
   */
  private function saveRuleAction($action, $tablesExist) {
    if ($tablesExist) {
      $columns = ['rule_id', 'action_id', 'ignore_condition_with_delay', 'is_active'];
      $values = ['%1', '%2', '%3', '%4'];
      $queryParams = [
        1 => [$action->rule_id, "Integer"],
        2 => [$action->action_id, "Integer"],
        3 => [$action->ignore_condition_with_delay, "Integer"],
        4 => [$action->is_active, "Integer"],
      ];
      $index = 4;
      $possibles = ['action_params', 'delay'];
      foreach ($possibles as $possible) {
        if ($action->$possible) {
          $index++;
          $columns[] = $possible;
          $values[] = "%" . $index;
          $queryParams[$index] = [$action->$possible, "String"];
        }
      }
      $insert = "INSERT INTO civirule_pre210_rule_action (" . implode(',', $columns) . ") VALUES(" . implode(',', $values). ")";
      CRM_Core_DAO::executeQuery($insert, $queryParams);
    }
    else {
      $actionArray = CRM_Civirules_Utils::moveDaoToArray($action);
      Civi::log()->warning(E::ts('Data from table civirule_rule_action: ') . json_encode($actionArray));
    }
  }

  /**
   * Method to save the rule condition data to either pre210 table or write in log
   *
   * @param $condition
   * @param $tablesExist
   */
  private function saveRuleCondition($condition, $tablesExist) {
    if ($tablesExist) {
      $columns = ['rule_id', 'condition_id', 'is_active'];
      $values = ['%1', '%2', '%3'];
      $queryParams = [
        1 => [$condition->rule_id, "Integer"],
        2 => [$condition->condition_id, "Integer"],
        3 => [$condition->is_active, "Integer"],
      ];
      $index = 3;
      $possibles = ['condition_link', 'condition_params'];
      foreach ($possibles as $possible) {
        if ($condition->$possible) {
          $index++;
          $columns[] = $possible;
          $values[] = "%" . $index;
          $queryParams[$index] = [$condition->$possible, "String"];
        }
      }
      $insert = "INSERT INTO civirule_pre210_rule_condition (" . implode(',', $columns) . ") VALUES(" . implode(',', $values). ")";
      CRM_Core_DAO::executeQuery($insert, $queryParams);
    }
    else {
      $conditionArray = CRM_Civirules_Utils::moveDaoToArray($condition);
      Civi::log()->warning(E::ts('Data from table civirule_rule_condition: ') . json_encode($conditionArray));
    }
  }

}

