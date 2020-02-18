## Post Trigger

The triggers can be 'post' type triggers that are checked in the `hook_civicrm_post` by the class method `CRM_CiviRules_Rule::processActiveRuleTrigger` independent of the objectName. This method will retrieve all active Rules in the CiviCRM installation, and process each trigger in the active rules. (Each rule can only be attached to a single Trigger).

The trigger will be directly linked to a CiviCRM entity (Individual, Contribution, Activity etc) and an operation (create, edit, delete, trash, restore) in whic case the trigger will be checked against the objectName and op in the post hook. The trigger can also be checked in a method (`CRM_CiviRules_xxxx::checkTrigger` where `xxxx` can be your own classname) that accepts a bunch of params (the objectName, objectId, op, objectRef, rule data and trigger data) and returns either TRUE if the trigger is triggered or FALSE is the trigger is not triggered.

## Cron Trigger

Trigger can also be 'cron' type which is processed in a separate cron job. In the current version of CiviRules an example is the Daily Trigger for Group Membership. This example class extends CRM_Civirules_Trigger_Cron.

 The cron type triggers are executed in a scheduled job that is added when you install the CiviRules extension:

<a href='../img/Civirules_cronjob.png'><img alt='Enable CiviRules CronJob' src='../img/Civirules_cronjob.png'/></a>

## TriggerData

TriggerData is a class used to hold data about the trigger and is passed to conditions and the execution of actions. Trigger data should at least hold a contact_id because all entities in Civi could be related back to a contact, and the objectRef of the post hook. Extra data could be added and retrieved by entity.

It is possible to create subclasses of `CRM_Civirules_TriggerData_TriggerData` to hold extra or custom data. Such an example is used upon comparison if a field is changed. The subclass `CRM_Civirules_TriggerData_Edit` holds the new data as the original data. The original data could then be used in a condition to compare the old field value with the new field value.