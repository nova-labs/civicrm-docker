<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-case_activity">
  <div class="crm-section">
    <div class="label">{$form.days_inactive.label}</div>
    <div class="content">{$form.days_inactive.html}</div>
    <div class="description">If there is no activity in the case for this given number of days, this condition would result to TRUE.</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
