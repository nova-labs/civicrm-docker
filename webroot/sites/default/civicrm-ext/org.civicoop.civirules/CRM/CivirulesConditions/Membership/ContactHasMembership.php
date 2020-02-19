<?php

class CRM_CivirulesConditions_Membership_ContactHasMembership extends CRM_Civirules_Condition {
  
  private $conditionParams = array();

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * This method returns true or false when an condition is valid or not
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   * @abstract
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    // To do add condition checking
    $sqlParams = array();
    $whereClauses = array();
    $whereClauses[] = "contact_id = %1";
    $sqlParams[1] = array($triggerData->getContactId(), 'Integer');
    if (count($this->conditionParams['membership_type_id'])) {
      switch ($this->conditionParams['type_operator']) {
        case 'in':
          $whereClauses[] = 'membership_type_id IN ('.implode($this->conditionParams['membership_type_id'], ','). ')';
          break;
        case 'not in':
          $whereClauses[] = 'membership_type_id NOT IN ('.implode($this->conditionParams['membership_type_id'], ','). ')';
          break;
      }
    }
    if (count($this->conditionParams['membership_status_id'])) {
      switch ($this->conditionParams['status_operator']) {
        case 'in':
          $whereClauses[] = 'status_id IN ('.implode($this->conditionParams['membership_status_id'], ','). ')';
          break;
        case 'not in':
          $whereClauses[] = 'status_id NOT IN ('.implode($this->conditionParams['membership_status_id'], ','). ')';
          break;
      }
    }
    
    $sql = "SELECT COUNT(*) as total FROM civicrm_membership WHERE ".implode($whereClauses, ' AND ');
    $count = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    if ($count) {
      return true;
    }
    return false;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contacthasmembership', 'rule_condition_id=' .$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $label = '';
    $operator_options = self::getOperatorOptions();
    
    try {
      $params = array(
        'is_active' => 1,
        'options' => array('limit' => 0, 'sort' => "name ASC"),
      );
      $membershipTypes = civicrm_api3('MembershipType', 'Get', $params);
      if (isset($this->conditionParams['membership_type_id']) && count($this->conditionParams['membership_type_id'])) {
        $operator = $operator_options[$this->conditionParams['type_operator']];
        $values = '';
        foreach ($this->conditionParams['membership_type_id'] as $membershipTypeId) {
          if (!isset($membershipTypes['values'][$membershipTypeId])) {
            continue;
          }
          if (strlen($values)) {
            $values .= ', ';
          }
          $values .= $membershipTypes['values'][$membershipTypeId]['name'];
        }
        $label .= ts('Membership type %1 %2', [
          1 => $operator,
          2 => $values,
        ]);
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    
    try {
      if (isset($this->conditionParams['membership_status_id']) && count($this->conditionParams['membership_status_id'])) {
        $params = [
          'options' => ['limit' => 0],
        ];
        $membershipStatus = civicrm_api3('MembershipStatus', 'Get', $params);
        $operator = $operator_options[$this->conditionParams['status_operator']];
        $values = '';
        foreach ($this->conditionParams['membership_status_id'] as $membershipStatusId) {
          if (!isset($membershipStatus['values'][$membershipStatusId])) {
            continue;
          }
          if (strlen($values)) {
            $values .= ', ';
          }
          $values .= $membershipStatus['values'][$membershipStatusId]['name'];
        }
        $label .= '<br> ' . ts('Membership status %1 %2', [
            1 => $operator,
            2 => $values,
          ]);
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    
    return trim($label);
  }
  
  /**
   * Method to get operators
   *
   * @return array
   * @access protected
   */
  public static function getOperatorOptions() {
    return array(
      'in' => ts('Is one of'),
      'not in' => ts('Is not one of'),
    );
  }

}