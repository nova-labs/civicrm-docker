<?php

class CRM_Civirules_ActionEngine_Factory {
	
	private static $instances = array();
	
	/**
	 * Returns the engine for executing actions.
	 * 
	 * @param array $ruleAction
	 *   Data from the ruleAction object.
	 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
	 *   Data from the trigger.
	 */
	public static function getEngine($ruleAction, CRM_Civirules_TriggerData_TriggerData $triggerData) {
		$id = $ruleAction['id'];
		if (!isset(self::$instances[$id])) {
			// This is the place where could add other engine to the system.
			self::$instances[$id] = new CRM_Civirules_ActionEngine_RuleActionEngine($ruleAction, $triggerData);
		}
		$engine = clone self::$instances[$id];
		$engine->setTriggerData($triggerData);
		return $engine;
	}
	
}
