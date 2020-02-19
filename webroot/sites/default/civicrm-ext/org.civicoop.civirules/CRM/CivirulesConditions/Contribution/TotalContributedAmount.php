<?php

class CRM_CivirulesConditions_Contribution_TotalContributedAmount extends CRM_CivirulesConditions_Generic_ValueComparison {

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    // Backwards compatibility: if contribution status is not set, assume it is the completed status.
    if (!isset($this->conditionParams['contribution_status_id'])) {
      $completed_status_id = civicrm_api3('OptionValue', 'getvalue', array('name' => 'completed', 'return' => 'value', 'option_group_id' => 'contribution_status'));
      $this->conditionParams['contribution_status_id'] = array($completed_status_id);
    }
  }

  /**
   * Returns value of the field
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return mixed
   * @access protected
   */
  protected function getFieldValue(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contact_id = $triggerData->getContactId();

    $completed_status_statement = "";
    $payment_instrument_statement = "";
    $financial_type_statement = "";
    $period_start_statement = "";
    $period_end_statement = "";

    if (count($this->conditionParams['contribution_status_id']) > 0) {
      $this->conditionParams['contribution_status_id'] = CRM_Utils_Type::escapeAll($this->conditionParams['contribution_status_id'], 'Integer');
      $completed_status_statement = " AND `contribution_status_id` IN (" . implode(",", $this->conditionParams['contribution_status_id']) . ")";
    }
    if (count($this->conditionParams['payment_instrument_id']) > 0) {
      $this->conditionParams['payment_instrument_id'] = CRM_Utils_Type::escapeAll($this->conditionParams['payment_instrument_id'], 'Integer');
      $payment_instrument_statement = " AND `payment_instrument_id` IN (" . implode(",", $this->conditionParams['payment_instrument_id']) . ")";
    }
    if (count($this->conditionParams['financial_type_id']) > 0) {
      $this->conditionParams['financial_type_id'] = CRM_Utils_Type::escapeAll($this->conditionParams['financial_type_id'], 'Integer');
      $financial_type_statement = " AND `financial_type_id` IN (" . implode(",", $this->conditionParams['financial_type_id']) . ")";
    }


    $periodStartDate = CRM_CivirulesConditions_Utils_Period::convertPeriodToStartDate($this->conditionParams);
    $periodEndDate = CRM_CivirulesConditions_Utils_Period::convertPeriodToEndDate($this->conditionParams);
    if ($periodStartDate) {
      $period_start_statement = " AND DATE(`receive_date`) >= '".$periodStartDate->format('Y-m-d')."'";
    }
    if ($periodEndDate) {
      $period_end_statement = " AND DATE(`receive_date`) <= '".$periodEndDate->format('Y-m-d')."'";
    }

    $sql = "SELECT SUM(`total_amount`)
            FROM `civicrm_contribution`
            WHERE  `contact_id` = %1
            {$completed_status_statement}
            {$payment_instrument_statement}
            {$financial_type_statement}
            {$period_start_statement}
            {$period_end_statement}
            ";
    $params[1] = array($contact_id, 'Integer');

    $total_amount = (float) CRM_Core_DAO::singleValueQuery($sql, $params);
    return $total_amount;
  }

  /**
   * Returns the value for the data comparison
   *
   * @return mixed
   * @access protected
   */
  protected function getComparisonValue() {
    switch ($this->getOperator()) {
      case '=':
      case '!=':
      case '>':
      case '>=':
      case '<':
      case '<=':
      case 'contains string':
        $key = 'value';
        break;
      case 'is one of':
      case 'is not one of':
      case 'contains one of':
      case 'not contains one of':
      case 'contains all of':
      case 'not contains all of':
        $key = 'multi_value';
        break;
    }

    if (!empty($this->conditionParams[$key])) {
      return $this->conditionParams[$key];
    } else {
      return '';
    }
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contribution_totalcontributedamount/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $userFriendlyConditionParams = parent::userFriendlyConditionParams();
    $period = CRM_CivirulesConditions_Utils_Period::userFriendlyConditionParams($this->conditionParams);

    $strParams = array();

    if (isset($this->conditionParams['financial_type_id']) && is_array($this->conditionParams['financial_type_id']) && count($this->conditionParams['financial_type_id']) >0) {
      $financial_types = self::getFinancialTypes();
      $strFinancialTypes = 'with financial type: ';
      $i = 0;
      foreach($this->conditionParams['financial_type_id'] as $finTypeId) {
        if ($i > 0) {
          $strFinancialTypes .= ', ';
        }
        $strFinancialTypes .= $financial_types[$finTypeId];
      }
      $strParams[] = $strFinancialTypes;
    }

    if (isset($this->conditionParams['payment_instrument_id']) && is_array($this->conditionParams['payment_instrument_id']) && count($this->conditionParams['payment_instrument_id']) >0) {
      $payment_instruments = self::getPaymentInstruments();
      $strPaidBy = 'paid by: ';
      $i = 0;
      foreach($this->conditionParams['payment_instrument_id'] as $payment_instrument) {
        if ($i > 0) {
          $strPaidBy .= ', ';
        }
        $strPaidBy .= $payment_instruments[$payment_instrument];
      }
      $strParams[] = $strPaidBy;
    }

    if (isset($this->conditionParams['contribution_status_id']) && is_array($this->conditionParams['contribution_status_id']) && count($this->conditionParams['contribution_status_id']) >0) {
      $statuses = self::getContributionStatus();
      $strStatus = 'with status: ';
      $i = 0;
      foreach($this->conditionParams['contribution_status_id'] as $status) {
        if ($i > 0) {
          $strStatus .= ', ';
        }
        $strStatus .= $statuses[$status];
      }
      $strParams[] = $strStatus;
    }

    if (count($strParams)) {
      $strParams = '('.implode(", ", $strParams).')';
    }

    return ts('Total amount').' '.$period.' '.$strParams.' '.$userFriendlyConditionParams;
  }

  public static function getPaymentInstruments() {
    $optionValues = civicrm_api3('OptionValue', 'Get', array('option_group_id' => 'payment_instrument', 'options' => array('limit' => 0)));
    $paymentInstruments = array();
    foreach ($optionValues['values'] as $paymentInstrument) {
      $paymentInstruments[$paymentInstrument['value']] = $paymentInstrument['label'];
    }
    return $paymentInstruments;
  }

  public static function getFinancialTypes() {
    return CRM_Civirules_Utils::getFinancialTypes();
  }

  public static function getContributionStatus() {
    $optionValues = civicrm_api3('OptionValue', 'Get', array('option_group_id' => 'contribution_status', 'options' => array('limit' => 0)));
    $statuses = array();
    foreach ($optionValues['values'] as $status) {
      $statuses[$status['value']] = $status['label'];
    }
    return $statuses;
  }

}