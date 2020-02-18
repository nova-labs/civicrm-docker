<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
/**
 * Trigger when a Contribution is changed or added.
 */
 class CRM_CivirulesPostTrigger_Contribution extends CRM_Civirules_Trigger_Post {

	/**
	 * Override alter trigger data. 
	 * 
	 * When a contribution is added/updated after an online payment is made
	 * contact_id and financial_type_id are not present in the data in the post hook.
	 * So we should retrieve this data from the database.
	 */
  public function alterTriggerData(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
  	try {
  		$dataFromPostHook = $triggerData->getEntityData('Contribution');
  		$dataInDatabase = civicrm_api3('Contribution', 'getsingle', array('id' => $dataFromPostHook['id']));
			// Merge both arrays preserving the data in the posthook.
			$newData = array_merge($dataInDatabase, $dataFromPostHook);
			$triggerData->setEntityData('Contribution', $newData);
		} catch (Exception $e) {
			// Do nothing. There could be an exception when the contribution does not exists in the database anymore.
		}
		
  	parent::alterTriggerData($triggerData);
	}

}
