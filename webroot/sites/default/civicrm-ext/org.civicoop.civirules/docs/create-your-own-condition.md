## Introduction

It is pretty simple (at least that is what we think) to add your own conditions to the CiviRules Engine. You can either do that by adding it to the CiviRules extension or by creating your own extension and including your own condition.

!!! Note
    __Check if CiviRules is installed__

    If you are adding conditions in your own extension it is a good idea to check if CiviRules is actually available in the installation part of your extension

In the tutorial here we will first add a simple condition without its own form processing (checking if the donation is the first donation of the donor) and then one which does have form processing (checking if the membership is of a certain type, so you need a form to select the membership type).

## Adding a Condition Without Form Processing

In this tutorial I will add a new condition that can be used in CiviRules. The new condition will be called **First Donation of Contact** and will answer the question: _is this the first contribution of the financial type Donation for the contact_? The related actions will only be executed if it is indeed the first donation of the contact.

In generic terms this condition is fairly simple: retrieve the contact_id and check if there are any contributions for the contact of the financial type Donation. If so, return FALSE else return TRUE. But now I have to add this to the CiviRules engine as a condition.

I am going to create my condition step by step.

1. make sure the condtion exists in the database
1. add a class to handle my condtion which extends the class `CRM_Civirules_Condition`
1. implement the required methods `isConditionValid` and `getExtraDataInputUrl`
1. link my condition to the entity Contribution with the method `requiredEntities`

### Step 1 - Add the Condition to the Database

You need to make sure that there is a record in the civirule_condition table for your condition. We recommend you do some by using an `insert` query.

If you have created your extension with Civix then you can add a file `/sql/createFirstDonation.sql` and add an `Upgrader` to your extension to process the sql file (check the relevant section of the [Developer Guide](https://docs.civicrm.org/dev/en/latest/extensions/civix/#generate-upgrader)).

The file `/sql/createFirstDonation.sql` should have this statement:

```mysql
  
INSERT INTO civirule_condition (name, label, class_name, is_active)
VALUES("first_donation_of_contact", "First Donation of a Contact", "CRM_CivirulesConditions_Contribution_FirstDonation", 1)

```

Obviously you can use any name you like for your `class_name`, we have stuck to our structure in this example with `CRM_CivirulesConditions_Contribution_FirstDonation` but that is not mandatory. 

!!! warning "On managed entities"
    We have had a few bad experiences using managed entities because the managed entities are always automatically re-created when you do a `clearcache` in drush or in the URL. And if you have just removed the managed entity because it is the cause of a problem that is not very helpful. So we have removed them from CiviRules. But it is possible to use a managed entity for a CiviRule action, we do not recommend it.


!!! Note
    You can also use the API to add a Condition to CiviRules. Entity is `CiviRuleCondition`, action is `Create`.
    
#### Alternative method json file (since CiviRules 2.9)

If your condition is in the CiviRules extension you can add your condition to the `sql/conditions.json` file.
When you have created the condition in your own extension you can add a `civirules_conditions.json` file in the root of your extension. And add the following data

```json

[
{
  "name": "first_donation_of_contact",
  "label": "First Donation of a Contact",
  "class_name": "CRM_CivirulesConditions_Contribution_FirstDonation"
}
]

```   

In your extension upgrader class add the following line:

```php

CRM_Civirules_Utils_Upgrader::insertConditionsFromJson($this->extensionDir . DIRECTORY_SEPARATOR . 'civirules_conditions.json');

```

The advantage of this alternative way is that it also checks whether the condition already exists and if so it updates the condition.

### Step 2 - Add a Class That Extends CRM_CiviRule_Condition

I create a PHP class called  <whatever namespace I like>, so in this example that will be CRM_CivirulesConditions_Contribution_FirstDonation. You can include the class file in the Civirules extension if you want, but you can also include it in your own extension. This class should extend `CRM_Civirules_Condition` to be able to add your condition to the CiviRules Engine (or a generic class, see note after the code).

If you are using an IDE (I use PhpStorm) you might get errors telling you class must be defined abstract or implement methods `isConditionValid` and `getExtraDataInputUrl`. If that is the case, you will get the answers in step 3.    

```php
class CRM_CivirulesConditions_Contribution_FirstDonation extends CRM_Civirules_Condition {
```

!!! Note
	Generic classes

    If you have a look in your extension you will see a folder CivirulesConditions. You will find simple conditions as their own class, like the one I am about to create here. You will also see a folder called Generic with generic classes you can use to create more complex examples.

### Step 3 - Implement the Mandatory Methods isConditionValid and getExtraDataInputUrl    

There are 2 mandatory methods that you need to implement in your class: `getExtraDataInputUrl` and `isConditionValid`.

Method `getExtraDataInputUrl` is used if you have additional forms for your condition, as is the case now. In this method you pass the url of the form you have created. The CiviRules Engine will pass control to this form when appropriate and make sure that the userContext to return to is in CiviRules. You will have to pass the rule_condition_id to the form url.

```php
/**
 * Returns a redirect url to extra data input from the user after adding a condition
 *
 * Return false if you do not need extra data input
 *
 * @param int $ruleConditionId
 * @return bool|string
 * @access public
 * @abstract
 */
public function getExtraDataInputUrl($ruleConditionId) {
  return CRM_Utils_System::url('civicrm/civirule/form/condition/membershiptype', 'rule_condition_id='
    .$ruleConditionId);
}
```
Method `isConditionValid` is called in the CiviRules engine to determine if the condition is met. It needs to receive a parameter triggerData that will be passed in with the object of the class `CRM_Civirules_TriggerData_TriggerData`. This object will hold methods that give me the contact_id and the data from the CiviCRM entity that is being processed.

The method should return `TRUE` or `FALSE`

So in this example I will get the contact_id of the event with the $triggerData->_getContactId method and use it to retrieve the contributions with financial_type_id 1 (for Donation) for the contact. In the simplest form this could be my entire method.

!!! Remark
    I check if there is more than 1 contribution because the Trigger is triggered in the CiviCRM post hook, so I already have one contribution, which is the first one.

```php
/**
 * Method is mandatory and checks if the condition is met
 *
 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
 * @return bool
 * @access public
 */
public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData)
{
  $contactId = $triggerData->getContactId();
  $contributionParams = array('contact_id' => $contactId, 'financial_type_id' => 1);
  $countContributions = civicrm_api3('Contribution', 'getcount', $contributionParams);
  switch ($countContributions) {
    case 0:
      return TRUE;
      break;
    case 1:
      $existingContribution = civicrm_api3('Contribution', 'Getsingle', array('contact_id' => $contactId));
      $triggerContribution = $triggerData->getEntityData('Contribution');
      if ($triggerContribution['contribution_id'] == $existingContribution['contribution_id']) {
        return TRUE;
      }
    break;
    default:
      return FALSE;
    break;
  }
}
```
If I want to be absolutely sure the contribution I retrieve is the one that has just been added I can compare the contribution_id of the contribution I have retrieved from the API is the same as the contribution_id of the contribution that has just been added.

To do that I will use the `$eventData->_getEntityData` method to retrieve the data from the entity just created and then compare it. It is a bit superfluous here, but it serves to show how you can use the `$triggerData->getEntityData` method.

### Step 4 - Validating whether the condition works with a certain trigger

If I use this condition, it only makes sense if I add this condition to Triggers that deal with some Entities like Individual or Contact. Adding the condition to check for the first contribution to a trigger that deals with GroupContact does not make sense.

The user interface of CiviRules has the ability to check whether your condition works with the given trigger. In this example I need data from the entity Contribution, so I add Contribution with the method `doesWorkWithTrigger` like this:

```php
/**
 * This function validates whether this condition works with the selected trigger.
 *
 * This function could be overriden in child classes to provide additional validation
 * whether a condition is possible in the current setup. E.g. we could have a condition
 * which works on contribution or on contributionRecur then this function could do
 * this kind of validation and return false/true
 *
 * @param CRM_Civirules_Trigger $trigger
 * @param CRM_Civirules_BAO_Rule $rule
 * @return bool
 */
public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
  return $trigger->doesProvideEntity('Contribution');
}

```

Ofcourse you can check other things in the `doesWorkWithTrigger` function. Such as whether the `$trigger` is a certain subclass.
For example the condition for Activity Status Changed checks whether the `$trigger` implements the interface `CRM_Civirules_TriggerData_Interface_OriginalData`:

```php

public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
  if ($trigger instanceof CRM_Civirules_TriggerData_Interface_OriginalData) {
    return $trigger->doesProvideEntity('Activity');
  }
  return false;
}

```

If you want to check whether the trigger provides multiple entities you can use the function `$trigger->doesProvideEntities`.


## Adding a Condition With Form Processing

In this tutorial I will add a new condition that can be used in CiviRules. The new condition will be called **Membership Type is (not) ....** and will answer the question: _is the membership (not) of the type specified_? The related actions will only be executed if it is indeed a membership of the specified type (or not of the specified type).

In generic terms this condition is fairly simple: retrieve the `membership_type_id` of the trigger entity, check if the type is equal/not equal to the one in the condition parameters and return TRUE or FALSE. But to be able to configure this condition I also need a form on which I can select the `membership_type_id` that I want to check against.

I am going to create my condition step by step.

1. make sure the condtion exists in the database
1. add a class to handle my condition which extends the class `CRM_Civirules_Condition`
1. implement the required methods `isConditionValid` and `getExtraDataInputUrl`
1. link my condition to the entity Contribution with the method requiredEntities
1. storing the condition data with method `setRuleConditionData`
1. use the method `userFriendlyConditionParams` to show the parameters on the CiviRule summary with a reasonably logic text
1. adding a form

I will also include the code of the actual form I will create to select the membership type.

### Step 1 - Add the Condition to the Database

In this tutorial I will add a new condition that can be used in CiviRules. The new condition will be called **Membership is (not) of Type** and will answer the question: _is the membership (not) if the type selected_? The related actions will only be executed if it is indeed of the specified type.

In generic terms this condition is fairly simple: Check if the membership just created, changed or deleted is/is one of/is not/is not one of the specified types. If so, return TRUE else return FALSE. But now I have to add this to the CiviRules engine as a condition. And I need a form to specify which membership types I want to check against.

I am going to create my condition step by step.

1. make sure the condtion exists in the database
1. add a class to handle my condtion which extends the class `CRM_Civirules_Condition`
1. implement the required methods `isConditionValid` and `getExtraDataInputUrl`
1. link my condition to the entity Contribution with the method `requiredEntities`


### Step 1 - Add the Condition to the Database

You need to make sure that there is a record in the civirule_condition table for your condition. We recommend you do some by using an `insert` query.

If you have created your extension with Civix then you can add a file `/sql/createMembershipType.sql` and add an `Upgrader` to your extension to process the sql file (check the relevant section of the [Developer Guide](https://docs.civicrm.org/dev/en/latest/extensions/civix/#generate-upgrader)).

The file `/sql/createMembershipType.sql` should have this statement:

```mysql
  
INSERT INTO civirule_condition (name, label, class_name, is_active)
VALUES("membership_is_of_type", "Membership is (not) of type(s)", "CRM_CivirulesConditions_Membership_Type", 1)

```

Obviously you can use any name you like for your `class_name`, we have stuck to our structure in this example with `CRM_CivirulesConditions_Membership_Type` but that is not mandatory. 

!!! warning "On managed entities"
    We have had a few bad experiences using managed entities because the managed entities are always automatically re-created when you do a `clearcache` in drush or in the URL. And if you have just removed the managed entity because it is the cause of a problem that is not very helpful. So we have removed them from CiviRules. But it is possible to use a managed entity for a CiviRule action, we do not recommend it.


!!! Note
    You can also use the API to add a Condition to CiviRules. Entity is `CiviRuleCondition`, action is `Create`.
    
#### Alternative method json file (since CiviRules 2.9)

If your condition is in the CiviRules extension you can add your condition to the `sql/conditions.json` file.
When you have created the condition in your own extension you can add a `civirules_conditions.json` file in the root of your extension. And add the following data

```json

[
{
  "name": "membership_is_of_type",
  "label": "Membership is (not) of type(s)",
  "class_name": "CRM_CivirulesConditions_Membership_Type"
}
]

```   

In your extension upgrader class add the following line:

```php

CRM_Civirules_Utils_Upgrader::insertConditionsFromJson($this->extensionDir . DIRECTORY_SEPARATOR . 'civirules_conditions.json');

```    

The advantage of this alternative way is that it also checks whether the condition already exists and if so it updates the condition.

### Step 2 - Add a Class That Extends CRM_CiviRule_Condition

I create a PHP class called  `<whatever namespace I like>`, so in this example that will be `CRM_CivirulesConditions_Membership_Type`. You can include the class file in the Civirules extension if you want, but you can also include it in your own extension. This class should extend `CRM_Civirules_Condition` to be able to add your condition to the CiviRules Engine (or a generic class, see note after the code).

If you are using an IDE (I use PhpStorm) you might get errors telling you class must be defined abstract or implement methods `isConditionValid` and `getExtraDataInputUrl`. If that is the case, you will get the answers in step 3.    

```php
class CRM_CivirulesConditions_Membership_Type extends CRM_Civirules_Condition {
```

!!! Extending
    If you have a look in your extension you will see a folder CivirulesConditions. You will find simple conditions as their own class, like the one I am about to create here. You will also see a folder called Generic with generic classes you can use to create more complex examples.

### Step 3 - Implement the Mandatory Methods isConditionValid and getExtraDataInputUrl

There are 2 mandatory methods that you need to implement in your class: `getExtraDataInputUrl` and `isConditionValid`.

Method `getExtraDataInputUrl` is used if you have additional forms for your condition, as is the case now. In this method you pass the url of the form you have created. The CiviRules Engine will pass control to this form when appropriate and make sure that the userContext to return to is in CiviRules. You will have to pass the `rule_condition_id` to the form url.    

```php
/**
 * Returns a redirect url to extra data input from the user after adding a condition
 *
 * Return false if you do not need extra data input
 *
 * @param int $ruleConditionId
 * @return bool|string
 * @access public
 * @abstract
 */
public function getExtraDataInputUrl($ruleConditionId) {
  return CRM_Utils_System::url('civicrm/civirule/form/condition/membershiptype', 'rule_condition_id='
    .$ruleConditionId);
}
```

Method `isConditionValid` is called in the CiviRules engine to determine if the condition is met. It needs to receive a parameter `triggerData` that will be passed in with the object of the class `CRM_Civirules_TriggerData_TriggerData`. This object will hold methods that give me the `contact_id` and the data from the CiviCRM entity that is being processed.

The method should return `TRUE` or `FALSE`.

So in this example I will get the `membership_type_id` of the entity Membership (to which condition will be linked to make any sense) and compare it to the one in the condition parameters, like so:

```php
/**
 * Method to determine if the condition is valid
 *
 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
 * @return bool
 */
public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
  $isConditionValid = FALSE;
  $membership = $triggerData->getEntityData('Membership');
  switch ($this->conditionParams['operator']) {
    case 0:
      if ($membership['membership_type_id'] == $this->conditionParams['membership_type_id']) {
        $isConditionValid = TRUE;
      }
    break;
    case 1:
      if ($membership['membership_type_id'] != $this->conditionParams['membership_type_id']) {
        $isConditionValid = TRUE;
      }
    break;
  }
  return $isConditionValid;
}

```
### Step 4 - Linking the Condition to the Entity with method requiredEntities

If I use this condition, it only makes sense if I add this condition to Triggers that deal with some Entities like Individual or Contact. Adding the condition to check for the membership type to a trigger that deals with Contribution does not make sense.

The user interface of CiviRules has the ability to check if you tell it what entity you need for your condition. In this example I need data from the entity Membership, so I add Contribution with the method _requiredEntities_  like this:

```php
/**
 * Returns an array with required entity names
 *
 * @return array
 * @access public
 */
public function requiredEntities() {
  return array('Membership');
}
```


### Step 5 - Setting the Condition Params

Storing the condition parameters in the database is done with the method `setRuleConditionData` like this:

```php
/**
 * Method to set the Rule Condition data
 *
 * @param array $ruleCondition
 * @access public
 */
public function setRuleConditionData($ruleCondition) {
  parent::setRuleConditionData($ruleCondition);
  $this->conditionParams = array();
  if (!empty($this->ruleCondition['condition_params'])) {
    $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
  }
}
```

### Step 6 - User Friendly Condition Parameters

To show the condition paramaters in a reasonably nice format as shown in this screenshot:

<a href='../img/CiviRules_createcondition_print01.png'><img alt='The overall picture' src='../img/CiviRules_createcondition_print01.png'/></a>

I use the method userFriendlyConditionParams:

```php
/**
 * Returns a user friendly text explaining the condition params
 * e.g. 'Older than 65'
 *
 * @return string
 * @access public
 */
public function userFriendlyConditionParams() {
  try {
    $membershipTypes = civicrm_api3('MembershipType', 'Get', array('is_active' => 1));
    $operator = null;
    if ($this->conditionParams['operator'] == 0) {
      $operator = 'equals';
    }
    if ($this->conditionParams['operator'] == 1) {
      $operator = 'is not equal to';
    }
    foreach ($membershipTypes['values'] as $membershipType) {
      if ($membershipType['id'] == $this->conditionParams['membership_type_id']) {
        return "Membership Type ".$operator." ".$membershipType['name'];
      }
    }
  } catch (CiviCRM_API3_Exception $ex) {}
  return '';
}
```

### Step 7 - Adding your Form

I now only need to create the form used to select the membership type. I will create this form using civix (generate:form) with the url that I have specified in the `getExtraDataInputUrl` method. The template of the form looks like this:

```html
<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-membership_type">
  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.membership_type_id.label}</div>
    <div class="content">{$form.membership_type_id.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
```

and the code like this. Note that I am extending the CRM_CivirulesConditions_Form_Form class which already does most of the CiviRules Engine stuff for me.

```php
/**
 * Class for CiviRules Condition Membership Type Form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */
 
class CRM_CivirulesConditions_Form_Membership_Type extends CRM_CivirulesConditions_Form_Form {
 
  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
 
    $membershipTypes = CRM_Civirules_Utils::getMembershipTypes();
    $membershipTypes[0] = ts('- select -');
    asort($membershipTypes);
    $this->add('select', 'membership_type_id', ts('Membership Type'), $membershipTypes, true);
    $this->add('select', 'operator', ts('Operator'), array('equals', 'is not equal to'), true);
 
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }
 
  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleCondition->condition_params);
    if (!empty($data['membership_type_id'])) {
      $defaultValues['membership_type_id'] = $data['membership_type_id'];
    }
    if (!empty($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
    }
    return $defaultValues;
  }
 
  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['membership_type_id'] = $this->_submitValues['membership_type_id'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}
```

