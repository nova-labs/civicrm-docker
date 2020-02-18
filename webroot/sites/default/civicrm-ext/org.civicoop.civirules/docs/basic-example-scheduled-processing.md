# Basic Example Scheduled Processing

## Introduction

Let us assume you want to check if the contacts in the group Regular Donors are actually still regular donors. So I want to check if they have contributed in the last 50 days, and if not I want to remove them from the group Regular Donors and add them to the group Downgraded Donors.

This is not a CiviRule I can attach to a trigger like 'Contact is added', this is one that has to be checked on a regular basis. For this you can use one of the triggers that are checked in the scheduled job civirules cron.

In order for this type of trigger to work you need to make sure the CiviRules cron job is active:

<a href='../img/CiviRules_cookbook_print11.png'><img src='../img/CiviRules_cookbook_print11.png'/></a>

## Setting up the basic CiviRule

So I now set up a CiviRule to make sure that an email is sent automatically every time a new contact is added AND the contact is added to the group Regular Donors. First step is to acces the CiviRules menu. If you install the extension the menu item will be added to the Administer menu in CiviCRM:

<a href='../img/CiviRules_cookbook_print02.png'><img src='../img/CiviRules_cookbook_print02.png'/></a>

If you click on the menu item you will get a list of existing CiviRules, with the ability to add one. If you click on Add CiviRule you will see a form with the basic data of your CiviRule:

<a href='../img/CiviRules_cookbook_print03.png'><img src='../img/CiviRules_cookbook_print03.png'/></a>

In this form you can add a title for you CiviRule. Spend some time thinking of a good title that all your users will understand and recognize. In this example you will use Downgrade Regular Donors if no longer Regular.

Next you have to select the trigger for the CiviRule. Each CiviRule can only have one trigger. Trigger are when the conditions are checked and the action(s) of the CiviRule are executed. In this example you will use the Daily Trigger for Group Members. On the next form you select the group Regular Donors, as they are the contacts that have to be checked whenever the scheduled job runs.

When I have entered this you will see the form where you can add conditions and actions to my CiviRule:

<a href='../img/CiviRules_cookbook_print12.png'><img src='../img/CiviRules_cookbook_print12.png'/></a>

## Adding the condition

When I click on Add Condition I get a form where I can select a condition. In this case I want to check when the last contribution from the contact was, so I select Last Contribution of Contact:

<a href='../img/CiviRules_cookbook_print13.png'><img src='../img/CiviRules_cookbook_print13.png'/></a>

In the next form I can add when the last contribution should be.

In the list I will get  a list of all conditions that are in the extension. Some conditions will be shipped with the initial extension, but you as a developer you can add you own conditions.

<a href='../img/CiviRules_cookbook_print14.png'><img src='../img/CiviRules_cookbook_print14.png'/></a>

If I now click Save my condition will be added to my CiviRule:

<a href='../img/CiviRules_cookbook_print15.png'><img src='../img/CiviRules_cookbook_print15.png'/></a>

## Adding Actions

In the example two actions have to be executed: the contact has to be removed from the group Regular Donors and added to the group Downgraded Donors. I click on Add Action and get a form where I can select Actions.

In the list I will get  a list of all actions that are in the extension. Some actions will be shipped with the initial extension, but you as a developer you can add you own actions.

You also get the option to add a delay, which means a certain time (number of minutes, days, weeks) to wait with the execution of the CiviRule. For example you can set a delay of 10 mins so typing errors are corrected before the CiviRule is executed. There is a tick box which allows you to specify if the condition is ONLY checked when the rule is triggered (tick the box) or BOTH when the rule is triggered AND when the action is executed (which could be days later depending on the delay - untick the box).

In this example I will first select the Remove Contact from Group action:

<a href='../img/CiviRules_cookbook_print16.png'><img src='../img/CiviRules_cookbook_print16.png'/></a>

and then hit Save and select the group Regular Donors. Next I do the same for the Add Contact to Group action and the group Downgraded Donors and I have completed the setup of my CiviRule: 

<a href='../img/CiviRules_cookbook_print17.png'><img src='../img/CiviRules_cookbook_print17.png'/></a>