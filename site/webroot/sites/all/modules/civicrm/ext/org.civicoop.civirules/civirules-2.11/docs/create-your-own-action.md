## Introduction

It is pretty simple (at least that is what we think) to add your own actions to the CiviRules Engine. You can either do that by adding it to the CiviRules extension or by creating your own extension and including your own action.

In the tutorial here we will first add a simple action without its own form processing (soft deleting a contact) and then one which does have form processing (setting the Thank You date for a contribution so you need a form to select the date).

As you will see the most basic form of action is a shell around a call to the CiviCRM API. We will demonstrate this in the third example, where a contact will be added to or removed from a group.

## Adding an Action Without Form Processing

In this tutorial I will add a new action that can be used in CiviRules. The new action will be called Soft Delete a Contact and will do just that, so set the contact record to is_deleted = 1.

In generic terms this action is fairly simple: retrieve the contact_id that is used in the trigger check (CiviRules checks triggers at the civicrm post hook) and soft delete the contact.

I am going to create this action step by step:

1. make sure the action exists in the database
1. add a class to handle my action which extends CRM_Civirules_Action
1. add the mandatory methods getExtraDataInputUrl and processAction

### Step 1 - Add the Action to the Database

You need to make sure that there is a record in the civirule_action table for your action. We recommend you do some by using an `insert` query.

If you have created your extension with Civix then you can add a file `/sql/createSoftDelete.sql` and add an `Upgrader` to your extension to process the sql file (check the relevant section of the [Developer Guide](https://docs.civicrm.org/dev/en/latest/extensions/civix/#generate-upgrader)).

The file `/sql/createSoftDelete.sql` should have this statement:

```mysql
         
INSERT INTO civirule_action (name, label, class_name, is_active) 
VALUES("contact_soft_delete", "Soft delete a contact", "CRM_CivirulesActions_Contact_SoftDelete", 1)

```

Obviously you can use any name you like for your `class_name`, we have stuck to our structure in this example with `CRM_CivirulesActions_Contact_SoftDelete` but that is not mandatory. 

!!! warning "On managed entities"
    We have had a few bad experiences using managed entities because the managed entities are always automatically re-created when you do a `clearcache` in drush or in the URL. And if you have just removed the managed entity because it is the cause of a problem that is not very helpful. So we have removed them from CiviRules. But it is possible to use a managed entity for a CiviRule action, we do not recommend it.


!!! Note
    You can also use the API to add an Action to CiviRules. Entity is `CiviRuleAction`, action is `Create`.
    
#### Alternative method json file (since CiviRules 2.9)

When your action is in the CiviRules extension you can add your condition to the `sql/actions.json` file.
When you have created the action in your own extension you can add a `civirules_actions.json` file in the root of your extension. And add the following data

```json

[
{
  "name": "contact_soft_delete",
  "label": "Soft delete a contact",
  "class_name": "CRM_CivirulesActions_Contact_SoftDelete"
}
]

```   

In your extension upgrader class add the following line:

```php

CRM_Civirules_Utils_Upgrader::insertActionsFromJson($this->extensionDir . DIRECTORY_SEPARATOR . 'civirules_actions.json');

```        

The advantage of this alternative way is that it also checks whether the action already exists and if so it updates the action.

### Step 2 - Add a Class That Extends CRM_CiviRule_Action

I create a PHP class called  <whatever namespace I like>, so in this example that will be CRM_CivirulesActions_Contact_SoftDelete. You can include the class file in the Civirules extension if you want, but you can also include it in your own extension. This class should extend CRM_Civirules_Action to be able to add your action to the CiviRules Engine (or a generic class, see note after the code).

If you are using an IDE (I use PhpStorm) you might get errors telling you class must be defined abstract or implement methods _processAction_ and _getExtraDataInputUrl_. If that is the case, you will get the answers in step 3.

```php
class CRM_CivirulesActions_Contact_SoftDelete extends CRM_Civirules_Action {
```

!!! Examples 
    If you have a look in your extension you will see a folder CivirulesActions. You will find simple actions as their own class, like the one I am about to create here. You will also see a folder called Generic with a generic class Api which can be used for API actions. For details, see the example Adding an Action Using the API.

### Step 3 - Add Mandatory Methods getExtraDataInputUrl and processAction

There are 2 mandatory methods that you need to implement in your class: `getExtraDataInputUrl` and `processAction`.

Method `getExtraDataInputUrl` can be used if you have additional forms for your action (check the next tutorial on this wiki page). If you do not need it, and I do not in this example, you can simply return `FALSE`. The method receives the parameter ruleConditionId. In code:

```php
/**
 * Method to return the url for additional form processing for action
 * and return false if none is needed
 *
 * @param int $ruleActionId
 * @return bool
 * @access public
 */
public function getExtraDataInputUrl($ruleActionId) {
  return FALSE;
}
```

Method `processAction` is called in the CiviRules engine to execute whatever your action needs to do. It needs to receive a parameter triggerData that will be passed in with the object of the class `CRM_Civirules_TriggerData_TriggerData`. Now you can do all sorts of complictated stuff in your processAction, or a simple basic API action. In this example we are soft deleting a contact, and using the `CRM_Contact_BAO_Contact::deleteContact` method to do so. (We could also have used the API but that is no fun for this example).

```php
/**
 * Method processAction to execute the action
 *
 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
 * @access public
 *
 */
public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
  $contactId = $triggerData->getContactId();
 
  //we cannot delete domain contacts
  if (CRM_Contact_BAO_Contact::checkDomainContact($contactId)) {
    return;
  }
 
  CRM_Contact_BAO_Contact::deleteContact($contactId);
}
```

## Adding an Action With Form Processing

In this tutorial I will add a new action that can be used in CiviRules. The new action will be called Set Subtype for Contact. It includes a form on which you can select the subtype the contact should be set to.

In generic terms this action is fairly simple: retrieve the contact_id that is used in the trigger check (CiviRules checks triggers at the civicrm post hook) and change the contact subtype to whatever the value in the action parameters is.

I am going to create this action step by step:

1. make sure the action exists in the database
1. add a class to handle my action which extends CRM_Civirules_Action
1. add the mandatory methods getExtraDataInputUrl and processAction
1. use the method userFriendlyConditionParams to show the parameters on the CiviRule summary with a reasonably logic text
1. add a form on which the contact subtype for the action can be selected

### Step 1 - Add the Action to the Database

You need to make sure that there is a record in the civirule_action table for your action. You need to make sure that there is a record in the civirule_action table for your action. We recommend you do some by using an `insert` query.

If you have created your extension with Civix then you can add a file `/sql/createSubtype.sql` and add an `Upgrader` to your extension to process the sql file (check the relevant section of the [Developer Guide](https://docs.civicrm.org/dev/en/latest/extensions/civix/#generate-upgrader)).

The file `/sql/createSubtype.sql` should have this statement:

```mysql
         
INSERT INTO civirule_action (name, label, class_name, is_active) 
VALUES("contact_sub_type", "Set subtype for a contact", "CRM_CivirulesActions_Contact_Subtype", 1)

```

Obviously you can use any name you like for your `class_name`, we have stuck to our structure in this example with `CRM_CivirulesActions_Contact_Subtype` but that is not mandatory. 

!!! warning "On managed entities"
    We have had a few bad experiences using managed entities because the managed entities are always automatically re-created when you do a `clearcache` in drush or in the URL. And if you have just removed the managed entity because it is the cause of a problem that is not very helpful. So we have removed them from CiviRules. But it is possible to use a managed entity for a CiviRule action, we do not recommend it.


!!! Note
    You can also use the API to add an Action to CiviRules. Entity is `CiviRuleAction`, action is `Create`.
    
#### Alternative method json file (since CiviRules 2.9)

When your action is in the CiviRules extension you can add your condition to the `sql/actions.json` file.
When you have created the action in your own extension you can add a `civirules_actions.json` file in the root of your extension. And add the following data

```json

[
{
  "name": "contact_sub_type",
  "label": "Set subtype for a contact",
  "class_name": "CRM_CivirulesActions_Contact_Subtype"
}
]

```   

In your extension upgrader class add the following line:

```php

CRM_Civirules_Utils_Upgrader::insertActionsFromJson($this->extensionDir . DIRECTORY_SEPARATOR . 'civirules_actions.json');

```        

The advantage of this alternative way is that it also checks whether the action already exists and if so it updates the action.

### Step 2 - Add a Class That Extends CRM_CiviRule_Action

I create a PHP class called  <whatever namespace I like>, so in this example that will be `CRM_CivirulesActions_Contact_Subtype`. You can include the class file in the Civirules extension if you want, but you can also include it in your own extension. This class should extend `CRM_Civirules_Action` to be able to add your action to the CiviRules Engine (or a generic class, see note after the code).

If you are using an IDE (I use PhpStorm) you might get errors telling you class must be defined abstract or implement methods `processAction`and `getExtraDataInputUrl`. If that is the case, you will get the answers in step 3.

```php
class CRM_CivirulesActions_Contact_Subtype extends CRM_Civirules_Action {
```

!!! Note
    If you have a look in your extension you will see a folder CivirulesActions. You will find simple actions as their own class, like the one I am about to create here. You will also see a folder called Generic with a generic class Api which can be used for API actions. For details, see the example [Adding an Action Using the API](#adding-an-action-using-the-api).


### Step 3 - Add Mandatory Methods getExtraDataInputUrl and processAction

Method `getExtraDataInputUrl` can be used if you have additional forms for your action like in this example. If you do not need it, you can simply return `FALSE`. The method receives the parameter `ruleActionId`. Obviously I will have to generate my form separately, and make sure that the url I include in this method is actually pointing to my form. In code for this example:

```php
/**
 * Method to return the url for additional form processing for action
 * and return false if none is needed
 *
 * @param int $ruleActionId
 * @return bool
 * @access public
 */
public function getExtraDataInputUrl($ruleActionId) {
  return CRM_Utils_System::url('civicrm/civirule/form/action/contact/subtype', 'rule_action_id='.$ruleActionId);
}
```
Method `processAction` is called in the CiviRules engine to execute whatever your action needs to do. It needs to receive a parameter `triggerData` that will be passed in with the object of the class `CRM_Civirules_TriggerData_TriggerData`. Now you can do all sorts of complictated stuff in your `processAction`, or a simple basic API action. In this example we are setting the contact subtype(s) for a contact, and using the `CRM_Contact_BAO_Contact::add` method to do so. (We could also have used the API but that is no fun for this example).

```php
/**
 * Method processAction to execute the action
 *
 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
 * @access public
 *
 */
public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
  $contactId = $triggerData->getContactId();
 
  $subTypes = CRM_Contact_BAO_Contact::getContactSubType($contactId);
  $contactType = CRM_Contact_BAO_Contact::getContactType($contactId);
 
  $changed = false;
  $action_params = $this->getActionParameters();
  foreach($action_params['sub_type'] as $sub_type) {
    if (CRM_Contact_BAO_ContactType::isExtendsContactType($sub_type, $contactType)) {
      $subTypes[] = $sub_type;
      $changed = true;
    }
  }
  if ($changed) {
    $params['id'] = $contactId;
    $params['contact_id'] = $contactId;
    $params['contact_type'] = $contactType;
    $params['contact_sub_type'] = $subTypes;
    CRM_Contact_BAO_Contact::add($params);
  }
}
```
### Step 4 - User Friendly Condition Parameters

To show the action paramaters in a reasonably nice format as shown in this screenshot:

<a href='../img/CiviRules_linked_action.png'><img src='../img/CiviRules_linked_action.png'/></a>

I use the method `userFriendlyCondtionParams`:

```php
/**
 * Returns a user friendly text explaining the condition params
 * e.g. 'Older than 65'
 *
 * @return string
 * @access public
 */
public function userFriendlyConditionParams() {
  $params = $this->getActionParameters();
  $label = ts('Set contact subtype to: ');
  $subTypeLabels = array();
  $subTypes = CRM_Contact_BAO_ContactType::contactTypeInfo();
  foreach($params['sub_type'] as $sub_type) {
    $subTypeLabels[] = $subTypes[$sub_type]['parent_label'].' - '.$subTypes[$sub_type]['label'];
  }
  $label .= implode(', ', $subTypeLabels);
  return $label;
}
```


### Step 5 - Add the Form

I now only need to create the form used to select the contact subtype. I will create this form using civix (generate:form) with the url that I have specified in the `getExtraDataInputUrl` method. The template of the form looks like this:

```html
<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-contact_subtype">
    <div class="crm-section">
        <div class="label">{$form.type.label}</div>
        <div class="content">{$form.type.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section sub_type-single">
        <div class="label">{$form.subtype.label}</div>
        <div class="content">{$form.subtype.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section sub_type-multiple" style="display: none;">
        <div class="label">{$form.subtypes.label}</div>
        <div class="content">{$form.subtypes.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
 
{literal}
    <script type="text/javascript">
        cj(function() {
            cj('select#type').change(triggerTypeChange);
 
            triggerTypeChange();
        });
 
        function triggerTypeChange() {
            cj('.sub_type-multiple').css('display', 'none');
            cj('.sub_type-single').css('display', 'none');
            var val = cj('#type').val();
            if (val == 0 ) {
                cj('.sub_type-single').css('display', 'block');
            } else {
                cj('.sub_type-multiple').css('display', 'block');
            }
        }
    </script>
{/literal} 

```
and the code like this. Note that I am extending the `CRM_CivirulesActionss_Form_Form` class which already does most of the CiviRules Engine stuff for me.

```php
/**
 * Class for CiviRules Group Contact Action Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */
 
class CRM_CivirulesActions_Contact_Form_Subtype extends CRM_CivirulesActions_Form_Form {
 
 
  /**
   * Method to get groups
   *
   * @return array
   * @access protected
   */
  protected function getSubtypes() {
    $subTypes = CRM_Contact_BAO_ContactType::contactTypeInfo();
    $options = array();
    foreach($subTypes as $name => $type) {
      if(!empty($type['parent_id'])) {
        $options[$name] = $type['parent_label'].' - '.$type['label'];
      }
    }
    return $options;
  }
 
  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');
 
    $this->add('select', 'type', ts('Single/Multiple'), array(
      0 => ts('Set one subtype'),
      1 => ts('Set multiple subtypes'),
    ));
 
    $this->add('select', 'subtype', ts('Contact sub type'), array('' => ts('-- please select --')) + $this->getSubtypes());
 
    $multiGroup = $this->addElement('advmultiselect', 'subtypes', ts('Contact sub types'), $this->getSubtypes(), array(
      'size' => 5,
      'style' => 'width:250px',
      'class' => 'advmultiselect',
    ));
 
    $multiGroup->setButtonAttributes('add', array('value' => ts('Add >>')));
    $multiGroup->setButtonAttributes('remove', array('value' => ts('<< Remove')));
 
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }
 
  public function addRules() {
    $this->addFormRule(array('CRM_CivirulesActions_Contact_Form_Subtype', 'validateSubtype'));
  }
 
  /**
   * Function to validate value of rule action form
   *
   * @param array $fields
   * @return array|bool
   * @access public
   * @static
   */
  static function validateSubtype($fields) {
    $errors = array();
    if ($fields['type'] == 0 && empty($fields['subtype'])) {
      $errors['subtype'] = ts('You have to select at least one subtype');
    } elseif ($fields['type'] == 1 && (empty($fields['subtypes']) || count($fields['subtypes']) < 1)) {
      $errors['subtypes'] = ts('You have to select at least one subtype');
    }
 
    if (count($errors)) {
      return $errors;
    }
    return true;
  }
 
  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleAction->action_params);
    if (!empty($data['sub_type'])) {
      $defaultValues['sub_type'] = reset($data['sub_type']);
      $defaultValues['sub_types'] = $data['sub_type'];
    }
    if (!empty($data['sub_type']) && count($data['sub_type']) <= 1) {
      $defaultValues['type'] = 0;
    } elseif (!empty($data['sub_type'])) {
      $defaultValues['type'] = 1;
    }
    return $defaultValues;
  }
 
  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['sub_type'] = array();
    if ($this->_submitValues['type'] == 0) {
      $data['sub_type'] = array($this->_submitValues['subtype']);
    } else {
      $data['sub_type'] = $this->_submitValues['subtypes'];
    }
 
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }
 
}
```

## Adding an Action Using the API

In this tutorial I will explain how to use the API when you are adding a CiviRule. I will not explain the details of how to add a CiviRule action with or without form processing, to see how to do that check the sections above: [Adding an Action Without Form Processing](#adding-an-action-without-form-processing) and [Adding an Action With Form Processing](#adding-an-action-with-form-processing).

The CiviRules extension has a class `CRM_CiviRulesActions_Generic_Api` which allows you to add a CiviRule action for an API Entity/Action very quickly. The class itself looks like this:

```php
abstract class CRM_CivirulesActions_Generic_Api extends CRM_Civirules_Action {
 
  /**
   * Method to get the api entity to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected abstract function getApiEntity();
 
  /**
   * Method to get the api action to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected abstract function getApiAction();
 
  /**
   * Returns an array with parameters used for processing an action
   *
   * @param array $parameters
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return array
   * @access protected
   */
  protected function alterApiParameters($parameters, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    //this method could be overridden in subclasses to alter parameters to meet certain criteria
    return $parameters;
  }
 
  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $entity = $this->getApiEntity();
    $action = $this->getApiAction();
 
    $params = $this->getActionParameters();
 
    //alter parameters by subclass
    $params = $this->alterApiParameters($params, $triggerData);
 
    //execute the action
    $this->executeApiAction($entity, $action, $params);
  }
 
  /**
   * Executes the action
   *
   * This method could be overridden if needed
   *
   * @param $entity
   * @param $action
   * @param $parameters
   * @access protected
   * @throws Exception on api error
   */
  protected function executeApiAction($entity, $action, $parameters) {
    try {
      civicrm_api3($entity, $action, $parameters);
    } catch (Exception $e) {
      $formattedParams = '';
      foreach($parameters as $key => $param) {
        if (strlen($formattedParams)) {
          $formattedParams .= ', ';
        }
        $formattedParams .= $key.' = '.$param;
      }
      throw new Exception('Civirules api action exception '.$entity.'.'.$action.' ('.$formattedParams.')');
    }
  }
 
}

```

If you in your code extend this class you basically only need to use a couple of methods to create an action:

- method `getApiEntity` to set the entity you want the API to use, for example Contribution
- method `getApiAction` to set the action you want the API to use, for example Change
- method `alterApiParameters` to set the parameters you want to pass to the API, for example thankyou_date = date('Ymd');

So this could be enough to construct a valid CiviRule action:

```php
/**
 * Class for CiviRules Set Thank You Date for Contribution Action
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */
class CRM_CivirulesActions_Contribution_ThankYouDate extends CRM_CivirulesActions_Generic_Api{
 
  /**
   * Method to set the api entity
   *
   * @return string
   * @access protected
   */
  protected function getApiEntity() {
    return 'Contribution';
  }
 
  /**
   * Method to set the api action
   *
   * @return string
   * @access protected
   */
  protected function getApiAction() {
    return 'Create';
  }
 
  /**
   * Returns an array with parameters used for processing an action
   *
   * @param array $params
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return array $params
   * @access protected
   */
  protected function alterApiParameters($params, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contribution = $triggerData->getEntityData("Contribution");
    $params['id'] = $contribution['id'];
    $params['thankyou_date'] = date('Ymd');
 
    return $params;
  }
 
  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return FALSE;
  }
}
```

