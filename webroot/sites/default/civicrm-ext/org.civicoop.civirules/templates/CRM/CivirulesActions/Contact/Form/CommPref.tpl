<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-contact_comm_pref">
  <h4>Communication Preferences:</h4>
  <div class="crm-section">
    <div class="label">{$form.on_or_off.label}</div>
    <div class="content">{$form.on_or_off.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.comm_pref.label}</div>
    <div class="content crm-select-container">{$form.comm_pref.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>