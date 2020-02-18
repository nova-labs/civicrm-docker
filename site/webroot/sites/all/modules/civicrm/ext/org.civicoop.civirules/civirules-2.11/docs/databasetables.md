
There are a couple of tables in the CiviRules extension. Their names will all start with civirule to have a clear distinction from the civicrm tables. The tables are:

| Table | Description|
|-|-|
|civirule_rule | holding the basic rule data like name, linked trigger and trigger_params |
| civirule_condition | holding the basic condtion data |
| civirule_action | holding the basic action data |
| civirule_trigger | holding the basic trigger data |
| civirule_rule_action | linking an action to a rule |
| civirule_rule_condition | linking a condition to a rule |  

## Database diagram

<a href='../img/CiviRules_ERD.png'><img src='../img/CiviRules_ERD.png'/></a>
