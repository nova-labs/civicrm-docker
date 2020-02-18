<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-activity">
  <div class="crm-section">
    <div class="label">{$form.relationship_type_id.label}</div>
    <div class="content">{$form.relationship_type_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.contact_id_b.label}</div>
    <div class="content">{$form.contact_id_b.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
