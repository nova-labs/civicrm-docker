{assign var='modifier' value="`$delayPrefix`modifier"}
{assign var='amount' value="`$delayPrefix`amount"}
{assign var='unit' value="`$delayPrefix`unit"}
{assign var='entity' value="`$delayPrefix`entity"}
{assign var='field' value="`$delayPrefix`field"}

<div class="label">{$form.$entity.label}</div>
<div class="content">{$form.$entity.html}</div>
<div class="clear"></div>
<div class="label">{$form.$field.label}</div>
<div class="content">{$form.$field.html}</div>
<div class="clear"></div>

<div class="label"></div>
<div class="content">{$form.$modifier.html} {$form.$amount.html} {$form.$unit.html}</div>
<div class="clear"></div>

{literal}
<script type="text/javascript">
var prefix = '{/literal}{$delayPrefix}{literal}';

cj(function() {
    var all_fields = cj('#'+prefix+'field').html();

    cj('#'+prefix+'entity').change(function() {
        var val = cj('#'+prefix+'entity').val();
        cj('#'+prefix+'field').html(all_fields);
        cj('#'+prefix+'field option').each(function(index, el) {
            if (cj(el).val().indexOf(val+'_') != 0) {
                cj(el).remove();
            }
        });
    });
    cj('#'+prefix+'entity').trigger('change');
});
</script>
{/literal}