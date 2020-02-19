<?php
/**
 * @author Klaas Eikelboom (klaas.eikelboom@civicoop.org)
 * @date 13-6-18
 * @license AGPL-3.0
 */

class CRM_Civirules_TrashRestoreApiWrapper implements API_Wrapper {

  private function isDeleted($contactId){
    return CRM_Core_DAO::singleValueQuery('select `is_deleted` from `civicrm_contact` where `id`=%1',array(
      1 => array($contactId,'Integer')
    ));

  }

  /**
   * Method to update request (required from abstract class)
   *
   * @param array $apiRequest
   * @return array $apiRequest
   */
  public function fromApiInput($apiRequest) {
    if(isset($apiRequest['id']) && isset($apiRequest['params']['is_deleted'])){
      $deleted = $apiRequest['params']['is_deleted'];
      $deletedDB = $this->isDeleted($apiRequest['params']['id']);
      if($deleted && !$deletedDB){
        // if a delete is asked and no delete in the database it is a trash
        // add it to the apiRequest to store it for the toApiOut
        $apiRequest['trashed'] = true;
      } elseif (!$deleted && $deletedDB){
        // if a not delete is asked and a delete is in the database it is restore
        $apiRequest['restored'] = true;
      };
    }
    return $apiRequest;
  }

  /**
   * @param array $apiRequest
   * @param array $result
   * @return array $result
   */
  public function toApiOutput($apiRequest, $result) {
    if(isset($apiRequest['trashed']) && $apiRequest['trashed']){
      $contact = CRM_Contact_BAO_Contact::findById($apiRequest['params']['id']);
      // the trash trigger is marked with an update action - so simulate this
      CRM_Civirules_Trigger_Post::post('update', $contact->contact_type, $contact->id, $contact);
    }
    if(isset($apiRequest['restored']) && $apiRequest['restored']){
      $contact = CRM_Contact_BAO_Contact::findById($apiRequest['params']['id']);
      // the trash trigger is marked with an update action - so simulate this
      CRM_Civirules_Trigger_Post::post('update', $contact->contact_type, $contact->id, $contact);
    }
    return $result;
  }

}