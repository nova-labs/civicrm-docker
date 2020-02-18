# CHANGELOG

## Version 2.12 (not yet released)

## Version 2.11

* Added action to create relationships.
* Added action to create a membership
* Added action to set financial type of a contribution
* Added condition to check whether a contribution is a recurring contribution
* fixed issue #53 (https://lab.civicrm.org/extensions/civirules/issues/53)
* fixed issue #46 (the is empty condition on the field value comparison is broken)
* fixed issue #59 (added triggers for campaign and condition campaign type)

## Version 2.10

* Added clone butten to the edit rule screen, so you can copy and change only what needs changing (#29)
* Added configuration for the record type for Activity and Case Activity trigger.
* Fixed bug in Activity and Case Activity trigger with an empty contact id.
* Added action to set Case Role
* Added trigger for new UFMatch record (link with CMS user is added)
* Removed the Case Added trigger as it causes errors (check https://lab.civicrm.org/extensions/civirules/issues/45). To do stuff when a new case is added use the Case Activity Added trigger instead with activity type Open Case. During the upgrade all existing rules based on the Case Added trigger will be deleted! They need to be recreated manually with the Case Activity is added trigger with activity type Open Case.
* Added condition Compare old participant status to new participant status

## Version 2.9

* Adds new action: update participant status
* Refactored the way triggers, actions and conditions are inserted in the database upon installation (#24).
* Fixed the fatal error after copying a profile (#19).
* Fixed php warning in CRM_CivirulesConditions_Contact_AgeComparison
* Fixed Cancel button on Rule form returns to "random" page (now it returns to rule overview)
* Fixed uncorrect behavior of isConditionValid with empty value (now returns FALSE)
* Fixed issue 40 (https://lab.civicrm.org/extensions/civirules/issues/40) where the fresh install SQL scripts still create tables with CONSTRAINT ON DELETE RESTRICT rather than ON DELETE CASCADE. There is an upgrade action (2025) linked to this fix which will remove the current constraints on tables civirule_rule_action, civirule_rule_condition and civirule_rule_tag and replace them with CONSTRAINT ON DELETE CASCADE and ON UPDATE RESTRICT.
* Introduces the option to take child groups into consideration for the condition 'contact is (not) in group'.

## Version 2.8
* "Set Thank You Date for a Contribution" action now supports options for time as well as date.
* Added trigger for Event Date reached.
* Added option to compare with original value in Field Value Comparison condition
* Add a condition for contacts being within a specific domain. This is useful for multisite installations as it allows rules to only be executed on contacts that are within that domain's domain_group_id

## Version 2.7
* Changed the ON DELETE NO ACTION to ON DELETE CASCADE for the constraints for tables civirule_rule_action, civirule_rule_condition, civirule_rule_tag which fixes #8
* Fixed notices and warnings on isRuleOnQueue method
* Add "show disabled rules" checkbox on filter for Manage Rules

## Version 2.6
REQUIRES MENU REBUILD! (/civicrm/clearcache)

* Added a trigger for membership end date
* Replaced the Find Rules custom search with a Manage Rules form
