<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-participant_statuschanged">
  <h2>Original value:</h2>
    <div class="crm-section">
        <div class="label">{$form.original_operator.label}</div>
        <div class="content">{$form.original_operator.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section section">
        <div class="label">
            {$form.original_status_id.label}
        </div>
        <div class="content crm-select-container" id="original_status_block">
            {$form.original_status_id.html}
        </div>
        <div class="clear"></div>
    </div>
  <h2>New value:</h2>
    <div class="crm-section">
        <div class="label">{$form.new_operator.label}</div>
        <div class="content">{$form.new_operator.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section section">
        <div class="label">
            {$form.new_status_id.label}
        </div>
        <div class="content crm-select-container" id="new_status_block">
            {$form.new_status_id.html}
        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
