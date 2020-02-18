<?php

abstract class CRM_Civirules_Delay_Delay {

  /**
   * Returns the DateTime to which an action is delayed to
   *
   * @param DateTime $date
   * @param CRM_Civirules_TriggerData_TriggerData
   * @return DateTime
   */
  abstract public function delayTo(DateTime $date, CRM_Civirules_TriggerData_TriggerData $triggerData);

  /**
   * Add elements to the form
   *
   * @param \CRM_Core_Form $form
   * @param prefix - The prefix for the form field name
   * @oaram CRM_Civirules_BAO_Rule $rule
   * @return mixed
   */
  abstract public function addElements(CRM_Core_Form &$form, $prefix, CRM_Civirules_BAO_Rule $rule);

  /**
   * Validate the values and set error message in $errors
   *
   * @param array $values
   * @param array $errors
   * @param prefix - The prefix for the form field name
   * @param CRM_Civirules_BAO_Rule $rule
   * @return void
   */
  abstract public function validate($values, &$errors, $prefix, CRM_Civirules_BAO_Rule $rule);

  /**
   * Set the values
   *
   * @param array $values
   * @param prefix - The prefix for the form field name
   * @param CRM_Civirules_BAO_Rule $rule
   * @return void
   */
  abstract public function setValues($values, $prefix, CRM_Civirules_BAO_Rule $rule);

  /**
   * Get the values
   *
   * @param prefix - The prefix for the form field name
   * @param CRM_Civirules_BAO_Rule $rule
   * @return array
   */
  abstract public function getValues($prefix, CRM_Civirules_BAO_Rule $rule);

  /**
   * Returns an description of the delay
   *
   * @return string
   */
  abstract public function getDescription();

  /**
   * Returns an explanation of the delay
   *
   * @return string
   */
  public function getDelayExplanation() {
    return $this->getDescription();
  }

  /**
   * Set default values
   *
   * @param $values
   * @param prefix - The prefix for the form field name
   * @param CRM_Civirules_BAO_Rule $rule
   */
  public function setDefaultValues(&$values, $prefix, CRM_Civirules_BAO_Rule $rule) {

  }

  /**
   * Returns the name of the template
   *
   * @return string
   */
  public function getTemplateFilename() {
    return str_replace('_',
        DIRECTORY_SEPARATOR,
        CRM_Utils_System::getClassName($this)
      ) . '.tpl';
  }

  /**
   * Returns the name
   *
   * @return string
   */
  public function getName() {
    return get_class($this);
  }

}