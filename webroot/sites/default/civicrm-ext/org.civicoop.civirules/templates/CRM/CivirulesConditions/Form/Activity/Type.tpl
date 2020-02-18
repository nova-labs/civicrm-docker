<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-contribution_activitytype">
  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section">
    <div class="label">
      <label for="activity_type-select">{ts}Activity Type(s){/ts}</label>
    </div>
    <div class="content crm-select-container" id="activity_type_block">
      {$form.activity_type_id.html}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>