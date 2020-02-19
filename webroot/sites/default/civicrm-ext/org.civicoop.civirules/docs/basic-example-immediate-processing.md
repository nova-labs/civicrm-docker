# CiviRules Basic Example Immediate Processing

## Introduction

Let's assume you always want to send a new regular donor an email thanking him or her for helping your organization in making the world a better place. In this example I assume that you always classify a regular donor when you add a new contact by adding him or her manually to the group 'Regular Donors'. In my local sandbox a new contact added to the group Regular Donors will look like this:

<a href='../img/CiviRules_cookbook_print01.png'><img src='../img/CiviRules_cookbook_print01.png'/></a>

## Setting up the basic CiviRule

So I now set up a CiviRule to make sure that an email is sent automatically every time a new contact is added AND the contact is added to the group Regular Donors. First step is to acces the CiviRules menu.

If you install the extension a new menu item will be added right of the Support menu:

<a href='../img/CiviRules_cookbook_print02.png'><img src='../img/CiviRules_cookbook_print02.png'/></a>

The Find Rules submenu item you will help you to search for a existing CiviRules. If you click on the New Rule submenu you will see a form with the basic data of your CiviRule:

<a href='../img/CiviRules_cookbook_print03.png'><img src='../img/CiviRules_cookbook_print03.png'/></a>

In this form you can add a title for your CiviRule. Spend some time thinking of a good title that all your users will understand and recognize. In this example we will use Send Welcoming Mail to New Regular Donors.

Next we have to select the trigger for the CiviRule. Each CiviRule can only have one trigger. The trigger is when the condition(s) are checked and the action(s) of the CiviRule are executed. In this example it is when a contact is added so the trigger is 'Contact of any type is added'. When I have entered this I click on the Next button. I will then go to the form where I can add conditions and actions to my CiviRule:

<a href='../img/CiviRules_cookbook_print04.png'><img src='../img/CiviRules_cookbook_print04.png'/></a>

## Adding the Condition

When I click on Add Condition I get a form where I can select a condition, like so:

<a href='../img/CiviRules_cookbook_print05.png'><img src='../img/CiviRules_cookbook_print05.png'/></a>

In the list I will get a list of all conditions that are in the extension. Some conditions will be shipped with the initial extension, but as a developer you can add your own conditions.

In this example I want to check if the new contact is member of the group Regular Donors so I select the condition Contact (not) in group. When I click save I can select the group I want and if the contact should be a member of the group or not be a member of the group(s):

<a href='../img/CiviRules_cookbook_print06.png'><img src='../img/CiviRules_cookbook_print06.png'/></a>

If I now click Save my condition will be added to my CiviRule:

<a href='../img/CiviRules_cookbook_print07.png'><img src='../img/CiviRules_cookbook_print07.png'/></a>

I can add more conditions if I want to, and link them with AND or OR. This is outside the scope of this example.

## Adding the Action

Finally I have to add the action that has to be executed if the condition(s) of my CiviRule are met. I click on Add Action and get a form where I can select Actions:

<a href='../img/CiviRules_cookbook_print08.png'><img src='../img/CiviRules_cookbook_print08.png'/></a>

In the list I will get a list of all actions that are in the extension. Some actions will be shipped with the initial extension, but as a developer you can add your own actions.

You also get the option to add a delay, which means a certain time (number of minutes, days, weeks) to wait with the execution of the CiviRule. For example you can set a delay of 10 mins so typing errors are corrected before the CiviRule is executed.

There is a tick box which allows you to specify if the condition is ONLY checked when the rule is triggered (tick the box) or BOTH when the rule is triggered AND when the action is executed (which could be days later depending on the delay - untick the box).

In this example I will select the action Send e-mail and leave the No Delay. If I click Save I get a form where I can enter details about the email:

<a href='../img/CiviRules_cookbook_print09.png'><img src='../img/CiviRules_cookbook_print09.png'/></a>

In this form you can enter the name and email-address that the email will be sent from, and the template that is used for the email. All are mandatory fields, so you will get an error if you leave them empty. Obviously you have already entered the template before setting up the CiviRule.

My CiviRule is now complete if I hit the Save button, and I will see a completed CiviRule in my form:

<a href='../img/CiviRules_cookbook_print10.png'><img src='../img/CiviRules_cookbook_print10.png'/></a>




