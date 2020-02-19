<?php

/**
 * Util functions for option groups.
 */
class CRM_Civirules_Utils_OptionGroup {

    /**
     * Returns which custom field html_type is a multiselect
     *
     * @param string $name
     * @param string $title
     * @params string $description
     * @return array|bool
     */
    public static function create($name, $title = "", $description = "") {

      if (self::exists($name) == TRUE) {
        return FALSE;
      }

      if (empty($name)) {
        return FALSE;
      }

      if (empty($title)) {
        $title = CRM_Civirules_Utils::buildLabelFromName($name);
      }
      $params = array(
        'name' => trim($name),
        'title' => trim($title),
        'description' => $description,
        'is_active' => 1,
        'is_reserved' => 1);
      try {
        $optionGroup = civicrm_api3('OptionGroup', 'create', $params);
      } catch (CiviCRM_API3_Exception $ex) {
        return FALSE;
      }
      return $optionGroup;
    }

  /**
   * Method to check if option group exists with name
   *
   * @param $name
   * @return bool
   */
  public static function exists($name) {
    $count = civicrm_api3('OptionGroup', 'getcount', array('name' => $name));
    if ($count > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Method to get option group with name
   *
   * @param $name
   * @return array
   * @throws Exception when error from API
   */
  public static function getSingleWithName($name) {
    if (empty($name)) {
      return array();
    }
    try {
      return civicrm_api3('OptionGroup', 'getsingle', array('name' => $name));
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }

  /**
   * Method to return all active option values for an option group
   *
   * @param $optionGroupId (can contain id or name of option grouop)
   * @return array
   */
    public static function getActiveValues($optionGroupId) {
      $result = array();
      try {
        $optionValues = civicrm_api3('OptionValue', 'get', array('option_group_id' => $optionGroupId));
        foreach ($optionValues['values'] as $optionValue) {
          $result[$optionValue['value']] = $optionValue['label'];
        }
      } catch (CiviCRM_API3_Exception $ex) {}
      return $result;
    }
}