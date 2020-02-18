<?php
/**
 * BAO RuleAction for CiviRule Rule Tag
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Civirules_BAO_RuleTag extends CRM_Civirules_DAO_RuleTag  {

  /**
   * Function to get values
   * 
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function getValues($params) {
    $result = array();
    $ruleTag = new CRM_Civirules_BAO_RuleTag();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $ruleTag->$key = $value;
        }
      }
    }
    $ruleTag->find();
    while ($ruleTag->fetch()) {
      $row = array();
      self::storeValues($ruleTag, $row);
      if (!empty($row['rule_id']) && !empty($row['rule_tag_id'])) {
        $result[$row['id']] = $row;
      } else {
        //invalid ruleTag because there is no linked tag or rule
        CRM_Civirules_BAO_RuleTag::deleteWithId($row['id']);
      }
    }
    return $result;
  }

  /**
   * Function to add or update rule tag
   * 
   * @param array $params 
   * @return array $result
   * @access public
   * @throws Exception when params is empty
   * @static
   */
  public static function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a civirule rule tag');
    }
    $ruleTag = new CRM_Civirules_BAO_RuleTag();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $ruleTag->$key = $value;
      }
    }
    $ruleTag->save();
    self::storeValues($ruleTag, $result);
    return $result;
  }

  /**
   * Function to delete a rule tag with id
   * 
   * @param int $ruleTagId
   * @throws Exception when ruleTagId is empty
   * @access public
   * @static
   */
  public static function deleteWithId($ruleTagId) {
    if (empty($ruleTagId)) {
      throw new Exception('rule tag id can not be empty when attempting to delete a civirule rule tag');
    }
    $ruleTag = new CRM_Civirules_BAO_RuleTag();
    $ruleTag->id = $ruleTagId;
    $ruleTag->delete();
    return;
  }

  /**
   * Function to delete all rule tags with rule id
   *
   * @param int $ruleId
   * @access public
   * @static
   */
  public static function deleteWithRuleId($ruleId) {
    $ruleTag = new CRM_Civirules_BAO_RuleTag();
    $ruleTag->rule_id = $ruleId;
    $ruleTagIds = [];
    $ruleTag->find();
    while ($ruleTag->fetch()) {
      $ruleTagIds[] = $ruleTag->id;
    }
    foreach ($ruleTagIds as $ruleTagId) {
      $ruleTag->id = $ruleTagId;
      $ruleTag->delete();
    }
  }

  /**
   * Function to delete all rule tags with tag id
   *
   * @param int $tagId
   * @access public
   * @static
   */
  public static function deleteWithTagId($tagId) {
    // todo : call from post trigger on delete of option value
    $ruleTag = new CRM_Civirules_BAO_RuleTag();
    $ruleTag->rule_tag_id = $tagId;
    $ruleTag->find(false);
    while ($ruleTag->fetch()) {
      $ruleTag->delete();
    }
  }

  /**
   * Method to build select list of all active rule tags
   *
   * @return array
   */
  public static function getRuleTagsList() {
    $result = array();
    $apiRuleTags = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => 'civirule_rule_tag',
      'options' => array('limit' => 0)
    ));
    foreach ($apiRuleTags['values'] as $ruleTagId => $ruleTagValues) {
      $result[$ruleTagValues['value']] = $ruleTagValues['label'];
    }
    asort($result);
    return $result;
  }

  /**
   * Method to get civirules tags for a specific rule
   *
   * @param $ruleId
   * @return array
   */
  public static function getTagLabelsForRule($ruleId) {
    $ruleTagLabels = array();
    try {
      $ruleTags = civicrm_api3('CiviRuleRuleTag', 'get', array(
        'rule_id' => $ruleId,
        'options' => array('limit' => 0)));
      foreach ($ruleTags['values'] as $ruleTagId => $ruleTag) {
        try {
          $ruleTagLabels[] = civicrm_api3('OptionValue', 'getvalue', array(
            'option_group_id' => 'civirule_rule_tag',
            'value' => $ruleTag['rule_tag_id'],
            'return' => 'label'
          ));
        } catch (CiviCRM_API3_Exception $ex) {
        }
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    return $ruleTagLabels;
  }
}