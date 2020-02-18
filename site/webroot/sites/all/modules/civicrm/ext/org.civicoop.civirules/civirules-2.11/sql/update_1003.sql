UPDATE `civirule_trigger` SET `class_name`='CRM_CivirulesPostTrigger_Activity' WHERE `class_name` = 'CRM_CivirulesPostEvent_Activity';
UPDATE `civirule_trigger` SET `class_name`='CRM_CivirulesPostTrigger_Contact' WHERE `class_name` = 'CRM_CivirulesPostEvent_Contact';
UPDATE `civirule_trigger` SET `class_name`='CRM_CivirulesPostTrigger_GroupContact' WHERE `class_name` = 'CRM_CivirulesPostEvent_GroupContact';
UPDATE `civirule_trigger` SET `class_name`='CRM_CivirulesCronTrigger_Birthday' WHERE `class_name` = 'CRM_CivirulesCronEvent_Birthday';
UPDATE `civirule_trigger` SET `class_name`='CRM_CivirulesCronTrigger_GroupMembership' WHERE `class_name` = 'CRM_CivirulesCronTrigger_GroupMembership';
UPDATE `civirule_trigger` SET `class_name`='CRM_CivirulesCronTrigger_GroupMembership' WHERE `class_name` = 'CRM_CivirulesCronEvent_GroupMembership';
