<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_CivirulesActions_Case_SetDateFieldOnCase extends CRM_Civirules_Action {

  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/case/setdatefield', 'rule_action_id='.$ruleActionId);
  }

  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $case = $triggerData->getEntityData('Case');
    $actionParameters = $this->getActionParameters();
    $isCustomField = false;
    $field = $actionParameters['field'];
    if (stripos($field, 'custom_')===0) {
      $isCustomField = true;
    }

    $date = new DateTime();
    $params = array();
    if (!empty($actionParameters['date'])) {
      $delayClass = unserialize(($actionParameters['date']));
      if ($delayClass instanceof CRM_Civirules_Delay_Delay) {
        $date = $delayClass->delayTo($date, $triggerData);
      }
    }

    if ($isCustomField) {
      if ($date instanceof DateTime) {
        $params[$field] = $date->format('Ymd');
        $params['entity_id'] = $case['id'];
        civicrm_api3('CustomValue', 'create', $params);
      }
    }
    else {
      if ($date instanceof DateTime) {
        $params[$field] = $date->format('Ymd');
        $params['id'] = $case['id'];
        civicrm_api3('Case', 'create', $params);
      }
    }
  }

  public static function getFields() {
    $return = array();
    $fields = civicrm_api3('Case', 'getfields', array('limit' => 99999));
    foreach ($fields['values'] as $field) {
      if (!isset($field['type'])) {
        continue;
      }
      if (!($field['type'] & CRM_Utils_Type::T_DATE)) {
        continue; //Field is not a Date field.
      }

      $fieldKey = $field['name'];
      if (isset($field['title'])) {
        $label = trim($field['title']);
      } elseif (isset($field['label'])) {
        $label = trim($field['label']);
      } else {
        $label = "";
      }
      if (empty($label)) {
        $label = $field['name'];
      }
      if (!empty($field['groupTitle'])) {
        $label = $field['groupTitle'].': '.$label;
      }
      $return[$fieldKey] = $label;
    }
    return $return;
  }

  public function userFriendlyConditionParams() {
    $actionParameters = $this->getActionParameters();
    $fields = self::getFields();
    $field = $actionParameters['field'];
    $label = 'Set '.$fields[$field].' to ';
    if (!empty($actionParameters['date'])) {
      $delayClass = unserialize(($actionParameters['date']));
      if ($delayClass instanceof CRM_Civirules_Delay_Delay) {
        $label .= $delayClass->getDelayExplanation();
      }
    } else {
      $label .= ' the date of processing of the action';
    }
    return $label;
  }

  /**
   * This function validates whether this action works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether an action is possible in the current setup.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $providedEntities = $trigger->getProvidedEntities();
    if (isset($providedEntities['Case'])) {
      return true;
    }
    return false;
  }

}