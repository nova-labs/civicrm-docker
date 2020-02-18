<?php

class CRM_CivirulesActions_Case_SetStatus extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $case = $triggerData->getEntityData("Case");
    $params = $this->getActionParameters();
    $params['id'] = $case['id'];
    $caseStatusOptionGroupId = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'case_status', 'return' => 'id'));
    $grouping = civicrm_api3('OptionValue', 'getvalue', array('value' => $params['status_id'], 'option_group_id' => $caseStatusOptionGroupId, 'return' => 'grouping'));

    // Set case end_date if we're closing the case. Clear end_date if we're (re)opening it.
    if ($grouping=='Closed') {
      if (empty($case['end_date'])) {
        $endDate = new DateTime();
        $params['end_date'] = $endDate->format('Ymd');
        // Update the case roles
        $relQuery = 'UPDATE civicrm_relationship SET end_date=%2 WHERE case_id=%1 AND end_date IS NOT NULL';
        $relParams = array(
          1 => array($case['id'], 'Integer'),
          2 => array($params['end_date'], 'Timestamp'),
        );
        CRM_Core_DAO::executeQuery($relQuery, $relParams);
      }
    } else {
      $params['end_date'] = '';

      // Update the case roles
      $relQuery = 'UPDATE civicrm_relationship SET end_date=NULL WHERE case_id=%1';
      $relParams = array(
        1 => array($case['id'], 'Integer'),
      );
      CRM_Core_DAO::executeQuery($relQuery, $relParams);
    }

    //execute the action
    $this->executeApiAction('Case', 'create', $params);
  }

  /**
   * Executes the action
   *
   * This method could be overridden if needed
   *
   * @param $entity
   * @param $action
   * @param $parameters
   * @access protected
   * @throws Exception on api error
   */
  protected function executeApiAction($entity, $action, $parameters) {
    try {
      civicrm_api3($entity, $action, $parameters);
    } catch (Exception $e) {
      echo $e->getMessage(); exit();
      $formattedParams = '';
      foreach($parameters as $key => $param) {
        if (strlen($formattedParams)) {
          $formattedParams .= ', ';
        }
        $formattedParams .= $key.' = '.$param;
      }
      throw new Exception('Civirules api action exception '.$entity.'.'.$action.' ('.$formattedParams.')');
    }
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/case/setstatus', 'rule_action_id='.$ruleActionId);
  }


  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $status = CRM_Case_PseudoConstant::caseStatus();
    return ts('Set case status to: %1',
              array(1 => $status[$params['status_id']]));
  }


  /**
   * Validates whether this action works with the selected trigger.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $entities = $trigger->getProvidedEntities();
    return isset($entities['Case']);
  }
}
