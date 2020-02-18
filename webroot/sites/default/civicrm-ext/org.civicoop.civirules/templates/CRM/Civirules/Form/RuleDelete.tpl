{* HEADER *}
<div class="crm-block crm-form-block crm-admin-options-form-block civirule_delete">
  <div class="messages status no-popup">
    {if $rule_in_queue eq TRUE}
      {ts}The rule with label <em>{$form.rule_label.value}</em> is still on the CiviCRM queue for a delayed action. When you confirm this queued action will be removed!{/ts}
      <br /><br />
    {/if}
    {ts}Are you sure you want to delete the rule with label{/ts} <em>{$form.rule_label.value}</em> {ts}from the database? The associated CiviRules logs will also be deleted.{/ts}<br /><br />
    {ts}If you would like to keep the CiviRules logs, press{/ts} <strong>{ts}Cancel{/ts}</strong>  {ts}and disable the Rule instead.{/ts}<br /><br />
    {ts}Press{/ts} <strong>{ts}Confirm{/ts}</strong> {ts}to really delete the rule from the database completely.{/ts}

  </div>
</div>
</div>

{* FOOTER *}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
