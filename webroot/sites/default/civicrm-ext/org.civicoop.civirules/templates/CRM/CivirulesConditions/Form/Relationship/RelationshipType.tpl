<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-relationship_type">
  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section relationship_type-section">
    <div class="label">
      <label for="relationship_type_id">{$form.relationship_type_id.label}</label>
    </div>
    <div class="content crm-select-container" id="relationship_type_block">
      {$form.relationship_type_id.html}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>