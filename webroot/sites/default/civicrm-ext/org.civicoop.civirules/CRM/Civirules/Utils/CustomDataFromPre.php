<?php

class CRM_Civirules_Utils_CustomDataFromPre {

  private static $customValues = array();

  public static function pre($op, $objectName, $objectId, $params) {
    if (!is_array($params)) {
      return;
    }
    if (isset($params['custom']) && is_array($params['custom'])) {
      foreach($params['custom'] as $custom_values) {
        foreach($custom_values as $id => $field) {
          $value = $field['value'];
          $fid = $field['custom_field_id'];
          self::setCustomData($objectName, $fid, $value, $id);
        }
      }
    }
    foreach($params as $key => $value) {
      if (stripos($key, 'custom_')===0) {
        // $key has the format of custom_45_34 or of custom_45
        // In the example above the 45 stands for the id of the custom field
        // and the 34 is the id of the record. The second number is not always
        // present and if it is not present we will treat the record number as a new one
        // and give it the id of -1.
        $customInfo = explode("_", $key, 3);
        if (count($customInfo) == 2) {
          list($custom_, $fid) = $customInfo;
          $id = -1; //It is a new value
        } elseif (count($customInfo) == 3) {
          list($custom_, $fid, $id) = $customInfo;
        } else {
          Throw new Exception('Field '.$key.' is invalid');
        }
        if (is_numeric($fid)) {
          // The variable $fid should contain a valid ID which should be a numeric value.
          self::setCustomData($objectName, $fid, $value, $id);
        }
      }
    }
  }

  private static function setCustomData($objectName, $field_id, $value, $id) {
    $v = $value;

    if (!is_numeric($field_id)) {
      return; // The parameter $field_id should contain a valid ID which is a numeric value.
    }

    /**
     * Convert value array from
     *   value_a => 1
     *   value_b => 1
     *
     * To
     *   [] => value_a
     *   [] => value_b
     *
     */
    if ($field_id > 0 && CRM_Civirules_Utils_CustomField::isCustomFieldMultiselect($field_id) && is_array($value)) {
      $all_ones = true;
      foreach($value as $i => $j) {
        if ($j != 1) {
          $all_ones = false;
        }
      }
      if ($all_ones) {
        $v = array();
        foreach($value as $i => $j) {
          $v[] = $i;
        }
      }
    }
    self::$customValues[$field_id][$id] = $v;
  }

  public static function addCustomDataToTriggerData(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    foreach(self::$customValues as $field_id => $values) {
      foreach($values as $id => $value) {
        $triggerData->setCustomFieldValue($field_id, $id, $value);
      }
    }
  }




}
