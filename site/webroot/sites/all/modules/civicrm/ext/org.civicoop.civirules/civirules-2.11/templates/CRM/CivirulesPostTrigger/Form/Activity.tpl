{crmScope extensionKey='org.civicoop.civirules'}
<h3>{$ruleTriggerHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-post-trigger-block-activity">
  <p class="help">
      {ts}When all contacts is selected then the trigger will be fired for every contact. Meaning that trigger might run more than once. {/ts}<br />
      {ts}When you don't want that select the record type for which you want to fire the trigger.{/ts}<br />
      {ts}The select record type also defines which conact is available in the conditions and actions.{/ts}
  </p>
    <div class="crm-section">
        <div class="label">{$form.record_type.label}</div>
        <div class="content">{$form.record_type.html}
        </div>
        <div class="clear">
        </div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}
