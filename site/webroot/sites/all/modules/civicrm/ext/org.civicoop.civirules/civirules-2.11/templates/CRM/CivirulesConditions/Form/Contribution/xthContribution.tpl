<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-xth_contribution">
  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section xth_contribution_fintype-section">
    <div class="label">{$form.financial_type.label}</div>
    <div class="content crm-select-container">{$form.financial_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section xth_contribution-section">
    <div class="label">{$form.number_contributions.label}</div>
    <div class="content">{$form.number_contributions.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>