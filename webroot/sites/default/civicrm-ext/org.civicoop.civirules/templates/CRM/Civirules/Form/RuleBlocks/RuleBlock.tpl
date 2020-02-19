{* block for rule data *}
<h3>Rule Details</h3>
<div class="crm-block crm-form-block crm-civirule-rule_label-block">
  <div class="crm-section">
    <div class="label">{$form.rule_label.label}</div>
    <div class="content">{$form.rule_label.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.rule_description.label}</div>
    <div class="content">{$form.rule_description.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.rule_tag_id.label}</div>
    <div class="content select-container">{$form.rule_tag_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.rule_help_text.label}</div>
    <div class="content">{$form.rule_help_text.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.rule_is_active.label}</div>
    <div class="content">{$form.rule_is_active.html}
    {if $clones}
        <br><span class="description font-red">{ts}This rule has the following duplicate(s) : {$clones}{/ts}</span>
        <br><span class="description font-red">{ts}Enabling can result in unintended double actions{/ts}</span>
    {/if}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.rule_created_date.label}</div>
    <div class="content">{$form.rule_created_date.value}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.rule_created_contact.label}</div>
    <div class="content">{$form.rule_created_contact.value}</div>
    <div class="clear"></div>
  </div>
  {$postRuleBlock}
</div>

{if $clones}
{literal}
<script>
  CRM.$('#rule_is_active').on('change',function(){
    if(this.checked){
        CRM.alert(CRM.ts('Enabling can result in unintended double actions'))
      }}
  );
</script>
{/literal}
{/if}
