<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-contact_in_group">
    <div class="help-block" id="help">
        <p>{ts}For this CiviRule condition you can specify:{/ts}</p>
        <p><strong>{ts}Operator{/ts}: </strong>{ts}if you want to check if the contact is in one group, more groups OR not in one or more groups{/ts}.</p>
        <p><strong>{ts}Check Group Tree{/ts}: </strong>{ts}when checking do you want to ONLY check the selected groups or ALSO check for inherited group membership (if the contact is in one of the child groups of the selected groups){/ts}.</p>
        <p><strong>{ts}Groups{/ts}: </strong>{ts}the groups to check for{/ts}.</p>
    </div>
    <div class="crm-section">
        <div class="label">{$form.operator.label}</div>
        <div class="content">{$form.operator.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.check_group_tree.label}</div>
        <div class="content">{$form.check_group_tree.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.group_ids.label}</div>
        <div class="content">{$form.group_ids.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
