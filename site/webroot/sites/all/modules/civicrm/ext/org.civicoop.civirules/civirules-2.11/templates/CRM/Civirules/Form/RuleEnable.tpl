{* HEADER *}
<div class="crm-block crm-form-block crm-admin-options-form-block civirule_enable">
  <div class="messages status no-popup">
    {ts}Are you sure you want to enable the rule with label{/ts} <em>{$form.rule_label.value}</em> <br/>
    {ts}This rule has the following duplicate(s){/ts} : {$clones}<br/>
    {ts}Press{/ts} <strong>{ts}Confirm{/ts}</strong> {ts}to enable but expect unintented double emails.{/ts}
  </div>
</div>
</div>

{* FOOTER *}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
