<?php

/**
 * Class to execute actions provided by civirules
 */
 
 class CRM_Civirules_ActionEngine_RuleActionEngine extends CRM_Civirules_ActionEngine_AbstractActionEngine {
 	
	/**
	 * @var CRM_Civirules_Action
	 */
	protected $actionClass;
	
	public function __construct($ruleAction, CRM_Civirules_TriggerData_TriggerData $triggerData) {
		parent::__construct($ruleAction, $triggerData);
		$this->actionClass = CRM_Civirules_BAO_Action::getActionObjectById($ruleAction['action_id'], true);
		if (!$this->ruleAction) {
			throw new Exception('Could not instanciate action for ruleAction with action_id: '.$ruleAction['action_id']);
		}
		$this->actionClass->setRuleActionData($ruleAction);
	}
 	
	/**
	 * Function to execute the rule action.
	 * 
	 * @return void
	 */
	public function execute() {
		$this->actionClass->processAction($this->triggerData);
	}
	
	/**
	 * Function to calculate the delay of the action.
	 * 
	 * @return void
	 */
	public function delayTo($delayedTo) {
		return $this->actionClass->delayTo($delayedTo, $this->triggerData);
	}
	
 }
