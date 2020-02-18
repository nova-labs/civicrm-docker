<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-contact_has_membership">
    <h4>{$form.membership_type_id.label}</h4>
    <div class="crm-section">
        <div class="label">{$form.type_operator.html}</div>
        <div class="content">{$form.membership_type_id.html}</div>
        <div class="clear"></div>
    </div>
    <h4>{$form.membership_status_id.label}</h4>
    <div class="crm-section">
        <div class="label">{$form.status_operator.html}</div>
        <div class="content">{$form.membership_status_id.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>