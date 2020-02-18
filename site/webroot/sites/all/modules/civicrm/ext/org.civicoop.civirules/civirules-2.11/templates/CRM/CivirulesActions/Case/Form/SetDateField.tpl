<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-setdateoncase">
    <div class="crm-section">
        <div class="label">{$form.field.label}</div>
        <div class="content">{$form.field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.date.label}</div>
        <div class="content">{$form.date.html}</div>
        <div class="clear"></div>
    </div>
    {foreach from=$delayClasses item=delayClass}
        <div class="crm-section crm-date-class" id="{$delayClass->getName()}">
            <div class="label"></div>
            <div class="content"><strong>{$delayClass->getDescription()}</strong></div>
            <div class="clear"></div>
            {include file=$delayClass->getTemplateFilename() delayPrefix='date'}
        </div>
    {/foreach}
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
    <script type="text/javascript">
        cj(function() {
            cj('select#date').change(triggerDelayChange);

            triggerDelayChange();
        });

        function triggerDelayChange() {
            cj('.crm-date-class').css('display', 'none');
            var val = cj('#date').val();
            if (val) {
                cj('#'+val).css('display', 'block');
            }
        }
    </script>
{/literal}