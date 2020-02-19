<?php
/**
 * Class for CiviRules Tag Id Form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 15 Nov 2017
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_EntityTag_TagId extends CRM_CivirulesConditions_Form_Form {

  /**
   * Method to get tags
   *
   * @return array
   * @access protected
   */
  private function getTags() {
    $result = array();
    try {
      $tags = civicrm_api3('Tag', 'get', array(
        'options' => array('limit' => 0),
      ));
      foreach ($tags['values'] as $tag) {
        $result[$tag['id']] = $tag['name'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return $result;
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $this->add('select', 'tag_id', ts('Tag(s)'), $this->getTags(), FALSE,
      array('id' => 'tag_id', 'multiple' => 'multiple', 'class' => 'crm-select2'));

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
    if (!empty($data['tag_id'])) {
      $defaultValues['tag_id'] = $data['tag_id'];
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
    $data['tag_id'] = $this->_submitValues['tag_id'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    parent::postProcess();
  }

  /**
   * Overridden parent method to add validation rules
   */
  public function addRules() {
    $this->addFormRule(array('CRM_CivirulesConditions_Form_EntityTag_TagId', 'validateTagAllowed'));
  }

  /**
   * Method to validate if the selected tags are actually used for contact
   *
   * @param $fields
   * @return bool|array
   */
  public static function validateTagAllowed($fields) {
    if (isset($fields['tag_id']) && !empty($fields['tag_id'])) {
      foreach ($fields['tag_id'] as $tagId) {
        try {
          $tag = civicrm_api3('Tag', 'getsingle', array(
            'id' => $tagId
          ));
          if (strpos($tag['used_for'], 'civicrm_contact') === FALSE) {
            $errors['tag_id'] = ts('Can not use the selected tag '.$tag['name'].' with contacts, condition only allowed for tags that are used for contacts');
            return $errors;
          }
        }
        catch (CiviCRM_API3_Exception $ex) {}
      }
    }
    return TRUE;
  }

}