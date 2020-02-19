<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-case_status">
  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.status_id.label}</div>
    <div class="content">{$form.status_id.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>