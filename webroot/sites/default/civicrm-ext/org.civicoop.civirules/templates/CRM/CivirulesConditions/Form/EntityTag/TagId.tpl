<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-tag_id">
    <div class="crm-section">
        <div class="label">{$form.tag_used_for.label}</div>
        <div class="content crm-select-container">{$form.tag_used_for.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.tag_id.label}</div>
        <div class="content crm-select-container">{$form.tag_id.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>