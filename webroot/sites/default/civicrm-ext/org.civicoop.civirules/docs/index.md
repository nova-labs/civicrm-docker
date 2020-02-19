# Introduction

CiviRules is an extension originally developed by [CiviCooP][civicoop] with funding from [MAF Norge][maf], [Amnesty International Flanders][amnesty], [Ilja de Coster][ilja] and CiviCooP themselves. 

The aim of the extension is to provide a rule-based engine to automate administrative processes like contact classification, sending of emails, adding contacts to groups, adding tags or activities to contacts etc. Although it has initially been developed with fundraising in mind, it could be used in many ways with CiviCRM entities like memberships, activities, mailings, contacts.....

Each CiviRule has three basic elements:

__Triggers__ that gets CiviRules into action. This will be things like:

- contribution is added
- contact is changed
- activity is deleted
- but also cron type where the CiviRule is executed when the CiviRules scheduled job runs.

__Conditions__ that determine the conditions to be compared when the trigger occurs. The conditions determine if the action will be executed and can be combined with AND or OR. This could be stuff like:

- when it is the first contribution of a donor
- when the contribution is of a certain financial type AND the total amount is more than xxx
- when the contact is member of group xxx.

__Actions__ that specify what is to happen if the trigger occurs and the conditions are met. Each CiviRule can have one or more actions. This could be:

- send an email, SMS or PDF
- add a contact to a group
- add an activity to a contact
- remove a tag from a contact

## Contents

This guide has two parts each aimed at a different audience.

- [Basic examples](basic-example-introduction) shows the CiviCRM administrator how she/he can configure CiviRules by means of a number of examples.
- [Create your own](create-your-own-introduction) shows the CiviCRM developer how she/he can expand the extension by creating own triggers, delays, and actions. 

## CiviCRM versions

CiviRules has been developed for CiviCRM 4.4 and has been tested with CiviCRM 4.6 (release 1.2), as this is the version our sponsors are on. If you want CiviRules updated to a newer version you can do so (check CiviRules on GitHub: https://github.com/CiviCooP/org.civicoop.civirules. Alternatively, if you want us to do it and have some funding, contact Jaap Jansma (jaap.jansma@civicoop.org) or Erik Hommel (erik.hommel@civicoop.org)

## Other useful extensions and modules

If you want to be able to send SMS, emails or create PDFs when a CiviRule is triggered, you will need to install the [SMS API extension](https://civicrm.org/extensions/sms-api), the [Email API extension](https://civicrm.org/extensions/e-mail-api) and the [PDF creation API extension](https://civicrm.org/extensions/pdf-creation-api) from https://civicrm.org/extensions.

To use webform submission as a trigger, you will need to download the [CiviRules webform Drupal module](https://lab.civicrm.org/partners/civicoop/webform_civirules).

These are not available through the user interface. For instructions on how to install them manually, please see the [System Administrator Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## History

There are a couple of blog post about the development of the extension (in descending publication date):

- [Extension CiviRules now avaible for download with live showcase from MAF Norge][blog1]
- [CiviRules - basic engine as the result of our first sprint][blog2]
- [First steps on the CiviRules road][blog3]
- [CiviRules sprint in January and March 2015][blog4]
- [Civi Rules!][blog5]


[civicoop]: http://www.civicoop.org/
[maf]:http://www.maf.no/
[amnesty]:https://www.aivl.be/
[ilja]:http://www.iljadecoster.be/

[blog1]:https://civicrm.org/blogs/erikhommel/extension-civirules-now-avaible-download-live-showcase-maf-norge
[blog2]:https://civicrm.org/blogs/erikhommel/civirules-basic-engine-result-our-first-sprint
[blog3]:https://civicrm.org/blogs/erikhommel/first-steps-civirules-road
[blog4]:https://civicrm.org/blogs/erikhommel/civirules-sprint-january-and-march-2015
[blog5]:https://civicrm.org/blogs/erikhommel/civi-rules
