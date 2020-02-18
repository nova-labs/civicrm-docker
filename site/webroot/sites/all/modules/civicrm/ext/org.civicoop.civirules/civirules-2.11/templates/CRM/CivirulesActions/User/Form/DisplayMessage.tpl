<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-message-display">
  <div id="title-block" class="crm-section">
    <div class="label">{$form.title.label}</div>
    <div class="content">{$form.title.html}</div>
    <div class="clear"></div>
  </div>
  <div id="message-block" class="crm-section">
    <div class="label">{$form.message.label}</div>
    <div class="content">{$form.message.html}</div>
    <div class="clear"></div>
  </div>
  <div id="type-block" class="crm-section">
    <div class="label">{$form.type.label}</div>
    <div class="content">{$form.type.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>