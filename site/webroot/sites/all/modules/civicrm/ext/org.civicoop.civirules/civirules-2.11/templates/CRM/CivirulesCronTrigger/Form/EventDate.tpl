<h3>{$ruleTriggerHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-cron_trigger-block-activity_date">
    <div class="crm-section">
        <div class="label">{$form.event_type_id.label}</div>
        <div class="content">{$form.event_type_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.date_field.label}</div>
        <div class="content">{$form.date_field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.enable_offset.label}</div>
        <div class="content">{$form.enable_offset.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section  hiddenElement" id="offsetSection">
        <div class="label">{$form.offset.label}</div>
        <div class="content">{$form.offset_type.html} {$form.offset.html} {$form.offset_unit.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<script type="text/javascript">
{literal}
cj(function() {
  cj('#enable_offset').change(function() {
    var isEnbaled = cj('#enable_offset').is(':checked');
    if (isEnbaled) {
      cj('#offsetSection').removeClass('hiddenElement');
    } else {
      cj('#offsetSection').addClass('hiddenElement');
    }
  });

  cj('#enable_offset').change();
});

{/literal}
</script>