Basic outline of the engine
There are three basic elements in CiviRules:

<ol>
<li>a <strong>trigger</strong> that triggers the checking of the conditions and might lead to actions being performed or scheduled, like 'Contribution is added' or 'Contact has changed'. There are two 'kinds' of triggers:
  <ol>
    <li>
        post triggers that will be checked in the CiviCRM post hook (independent of entity) and will be executed immediately (although the delay might ....well....delay)
    </li>
    <li>
       cron triggers that will be checked and executed by a scheduled job that will be added to the scheduled job (and set to Daily) when you install the CiviRules extension
    </li>
  </ol>
</li>      
<li>
a series of <strong>condition(s)</strong> that can be checked and in total will determine if the linked action will be executed. Each condition will be answered with either FALSE or TRUE. Conditions can be linked with AND or OR (although you can not combine them in a (condition A AND condition B) OR (condition C AND condition D))
</li>
<li>
an <strong>action</strong> that will be executed if the trigger is triggered and all conditions apply. The action will in principle link to a combination of API entity, API action and a set of parameters (but you can deviate from this). The action can either be executed immediately or added to a task queue with a delay (which is then processed by another scheduled job that is added when you install CiviRules and set to run 'Always').
</il>
</ol>

Find more detailed information in the tutorials:

- [A Little More About Trigger](trigger)
- [Database Tables](databasetables)
- [Create your own delay](create-your-own-delay)
- [Create your own condition](create-your-own-condition)
- [Create your own action](create-your-own-action)
- [Add logging](add-logging)
