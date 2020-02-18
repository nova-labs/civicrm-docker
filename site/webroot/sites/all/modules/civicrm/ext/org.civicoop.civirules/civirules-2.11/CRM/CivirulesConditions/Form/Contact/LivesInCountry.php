<?php
/**
 * Class for CiviRules Contact Lives in Country Form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 June 2018
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Contact_LivesInCountry extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
    $this->add('select', 'country_id', ts('Country'), $this->getCountries(), TRUE,
      array('id' => 'country_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('select', 'location_type_id', ts('Location Type of the Address to Test'), $this->getLocationTypes(), FALSE);
    $this->add('checkbox','no_address_found', ts('Use CiviCRM Default Country if Contact has no Address'));
    $this->add('checkbox','no_country_found', ts('Use CiviCRM Default Country if Address has no Country'));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleCondition->condition_params);
    if (!empty($data['country_id'])) {
      $defaultValues['country_id'] = $data['country_id'];
    }
    if (!empty($data['location_type_id'])) {
      $defaultValues['location_type_id'] = $data['location_type_id'];
    }
    if ($this->_action == CRM_Core_Action::ADD) {
      $defaultValues['no_address_found'] = TRUE;
      $defaultValues['no_country_found'] = TRUE;
    }
    else {
      if (isset($data['no_address_found']) && $data['no_address_found'] == 1) {
        $defaultValues['no_address_found'] = TRUE;
      }
      else {
        $defaultValues['no_address_found'] = FALSE;
      }
      if (isset($data['no_country_found']) && $data['no_country_found'] == 1) {
        $defaultValues['no_country_found'] = TRUE;
      }
      else {
        $defaultValues['no_country_found'] = FALSE;
      }
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['country_id'] = $this->_submitValues['country_id'];
    $data['location_type_id'] = $this->_submitValues['location_type_id'];
    $data['no_address_found'] = $this->_submitValues['no_address_found'];
    $data['no_country_found'] = $this->_submitValues['no_country_found'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }

  /**
   * Method to get the country list
   *
   * @return array
   */
  private function getCountries() {
    $countries = array();
    try {
      $apiCountries = civicrm_api3('Country', 'get', array(
        'return' => array("id", "name"),
        'options' => array('limit' => 0, 'sort' => "name"),
        ));
      foreach ($apiCountries['values'] as $apiCountryId => $apiCountry) {
        $countries[$apiCountryId] = $apiCountry['name'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return $countries;
  }

  /**
   * Method to get the location type list
   *
   * @return array
   */
  private function getLocationTypes() {
    $locationTypes = array(0 => '-- please select --');
    try {
      $apiLocationTypes = civicrm_api3('LocationType', 'get', array(
        'return' => array("id", "display_name"),
        'is_active' => 1,
        'options' => array('limit' => 0, 'sort' => "display_name"),
        ));
      foreach ($apiLocationTypes['values'] as $apiLocationTypeId => $apiLocationType) {
        $locationTypes[$apiLocationTypeId] = $apiLocationType['display_name'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return $locationTypes;
  }

}