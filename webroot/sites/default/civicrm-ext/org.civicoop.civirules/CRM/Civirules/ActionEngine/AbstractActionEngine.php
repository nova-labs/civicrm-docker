<?php

abstract class CRM_Civirules_ActionEngine_AbstractActionEngine {
	
	/**
	 * @var array
	 */
	protected $ruleAction;
	
	/**
	 * @var CRM_Civirules_TriggerData_TriggerData
	 */
	protected $triggerData;
	
	/**
	 * Function to execute the rule action.
	 * 
	 * @return void
	 */
	abstract public function execute();
	
	/**
	 * Function to calculate the delay of the action.
	 * 
	 * @param $delayedTo
	 * @return false|DateTime
	 */
	abstract public function delayTo($delayedTo);
	
	/**
	 * @param array $ruleAction
	 *   Data from the ruleAction object.
	 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
	 *   Data from the trigger.
	 */
	public function __construct($ruleAction, CRM_Civirules_TriggerData_TriggerData $triggerData) {
		$this->ruleAction = $ruleAction;
		$this->triggerData = $triggerData;
	}
	
	/**
	 * @return array
	 */
	public function getRuleAction() {
		return $this->ruleAction;
	}
	
	/**
	 * @return CRM_Civirules_TriggerData_TriggerData
	 */
	public function getTriggerData() {
		return $this->triggerData;
	}
	
	/**
	 * Sets the trigger data
	 * 
	 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
	 * 
	 * @return CRM_Civirules_ActionEngine_AbstractActionEngine
	 */
	public function setTriggerData(CRM_Civirules_TriggerData_TriggerData $triggerData) {
		$this->triggerData = $triggerData;
		return $this;
	}
	
}
