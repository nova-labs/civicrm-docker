<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-value-comparison">
    {include file="CRM/CivirulesConditions/Form/Utils/Period.tpl"}
    <div class="crm-section">
        <div class="label">{$form.operator.label}</div>
        <div class="content">{$form.operator.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.value.label}</div>
        <div class="content">{$form.value.html}</div>
        <div class="clear"></div>
    </div>

    <div class="crm-section">
        <div class="label">{$form.financial_type_id.label}</div>
        <div class="content">{$form.financial_type_id.html}<br /><span class="description">{ts}If you dont select any then it means of any financial type{/ts}</span></div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.payment_instrument_id.label}</div>
        <div class="content">{$form.payment_instrument_id.html}<br /><span class="description">{ts}If you dont select any then it means of any payment instrument{/ts}</span></div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.contribution_status_id.label}</div>
        <div class="content">{$form.contribution_status_id.html}<br /><span class="description">{ts}If you dont select any then it means of any contribution status{/ts}</span></div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>