<?php
/**
 * Class for CiviRules Condition Contact Lives in Country
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 June 2018
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Contact_LivesInCountry extends CRM_Civirules_Condition {

  private $_conditionParams = array();

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->_conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->_conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method to determine if condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $return = FALSE;
    $contactId = $triggerData->getContactId();
    if ($contactId) {
      // retrieve country (primary or based on location_type)
      try {
        if (isset($this->_conditionParams['location_type_id']) && !empty($this->_conditionParams['location_type_id'])) {
          $countryParams = array(
            'return' => 'country_id',
            'location_type_id' => $this->_conditionParams['location_type_id'],
            'contact_id' => $contactId,
          );
        }
        else {
          $countryParams = array(
            'return' => 'country_id',
            'is_primary' => TRUE,
            'contact_id' => $contactId,
          );
        }
        $countryId = civicrm_api3('Address', 'getvalue', $countryParams);
        // if empty country setting determines if default country is to be used
        if (empty($countryId)) {
          $countryId = $this->checkDefaultCountryUsed();
        }
      }
      // if no address found setting determines if default country is to be used
      catch (CiviCRM_API3_Exception $ex) {
        $countryId = $this->checkDefaultCountryUsed();
      }
      if ($countryId) {
        if (in_array($countryId, $this->_conditionParams['country_id'])) {
          $return = TRUE;
        }
      }
    }
    return $return;
  }

  /**
   * Method to return the default localization country
   *
   * @return array|bool
   */
  private function checkDefaultCountryUsed() {
    if ($this->_conditionParams['no_address_found'] || $this->_conditionParams['no_address_found']) {
      try {
        return civicrm_api3('Setting', 'getvalue', array(
          'name' => "defaultContactCountry",
        ));
      }
      catch (CiviCRM_API3_Exception $ex) {
        return FALSE;
      }
    }
  }

  /**
   * Method to set the where clauses
   *
   * @param $fieldName
   */
  private function addWhereClauses($fieldName) {
    $fieldIds = array();
    foreach ($this->_conditionParams[$fieldName] as $fieldValue) {
      $this->_index++;
      $fieldIds[] = '%'.$this->_index;
      $this->_queryParams[$this->_index] = array($fieldValue, 'Integer');
    }
    if (!empty($fieldIds)) {
      $this->_query .= ' AND act.' . $fieldName . ' IN (' . implode(', ', $fieldIds) . ')';
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
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contact/livesincountry', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $countryNames = array();
    foreach ($this->_conditionParams['country_id'] as $countryId) {
      try {
        $countryNames[] = civicrm_api3('Country', 'getvalue', array(
          'country_id' => $countryId,
          'return' => 'name',
        ));
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    if (!empty($countryNames)) {
      $text = ts('lives in one of') . ': ' . implode('; ', $countryNames);
    }
    else {
      $text = ts('lives in one of') . ': ' . implode('; ', $this->_conditionParams['country_id']);

    }
    if (isset($this->_conditionParams['location_type_id']) && !empty($this->_conditionParams['location_type_id'])) {
      try {
        $text .= ' (checking address with location type ' . civicrm_api3('LocationType', 'getvalue', array(
          'return' => 'display_name',
          'id' => $this->_conditionParams['location_type_id'],
        )) . ')';
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    else {
      $text .= ' (checking primary address)';
    }
    if ($this->_conditionParams['no_address_found']) {
      $text .= ', using default country if contact has no address';
    }
    if ($this->_conditionParams['no_address_found']) {
      $text .= ', using default country if address has no country';
    }
    return $text;
  }

}