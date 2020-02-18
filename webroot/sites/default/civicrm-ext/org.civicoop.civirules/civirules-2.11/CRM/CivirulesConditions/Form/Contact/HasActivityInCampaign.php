<?php
/**
 * Class for CiviRules Contact Has Activity of Type(s) in Campaign(s) Form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 3 May 2018
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_Contact_HasActivityInCampaign extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
    $this->add('select', 'activity_type_id', ts('Activity Type(s)'), $this->getActivityTypeList(), TRUE,
      array('id' => 'activity_type_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('select', 'campaign_id', ts('Campaign(s)'), CRM_Civirules_Utils::getCampaignList(), TRUE,
      array('id' => 'campaign_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
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
    if (!empty($data['activity_type_id'])) {
      $defaultValues['activity_type_id'] = $data['activity_type_id'];
    }
    if (!empty($data['campaign_id'])) {
      $defaultValues['campaign_id'] = $data['campaign_id'];
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
    $data['activity_type_id'] = $this->_submitValues['activity_type_id'];
    $data['campaign_id'] = $this->_submitValues['campaign_id'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    parent::postProcess();
  }

  /**
   * Method to get the activity type list
   *
   * @return array
   */
  private function getActivityTypeList() {
    $activityTypeList = array();
    try {
      $activityTypes = civicrm_api3('OptionValue', 'get', array(
        'sequential' => 1,
        'is_active' => 1,
        'option_group_id' => 'activity_type',
        'component_id' => array('IS NULL' => 1),
        'options' => array('limit' => 0),
      ));
      foreach ($activityTypes['values'] as $activityType) {
        $activityTypeList[$activityType['value']] = $activityType['label'];
      }
      asort($activityTypeList);
    }
    catch (CiviCRM_API3_Exception $ex) {
      $activityTypeList = array();
    }
    return $activityTypeList;
  }

}