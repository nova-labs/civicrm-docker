<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-contact_privacy_options">
  <h4>Privacy:</h4>
  <div class="crm-section">
    <div class="label">{$form.on_or_off.label}</div>
    <div class="content">{$form.on_or_off.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.privacy_options.label}</div>
    <div class="content crm-select-container">{$form.privacy_options.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>