<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-activity_date">
  <div class="help-block" id="help">
    {ts}You can test the activity date against specific dates (Comparison Date or From and To Date depending on the Operator).{/ts}
    <br /><br />
    {ts}You can also select to test against either the date the rule is triggered or the date the action is executed. If you do not use delayed actions this is the same date but if you do use a delay there is a diffderence!{/ts}
    <br />
    {ts}For example, if the rule is triggered by a new activity on the 1 April but the action is executed with a delay of 1 day, comparing with the date the rule is triggered will compare with 1 April whilst comparing with the date the action is executed will compare with 2 April (if you did NOT check the <em>Don't recheck condition upon processing of delayed action!</em> box when defining the delay){/ts}
    <br />
    {ts}Please note that using the date the action is executed only makes sense if you also specify a delay!!!!{/ts}
  </div>

  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content operator">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-compare-date">
    <div class="label">{$form.activity_compare_date.label}</div>
    <div class="content activity-date-comparison">{$form.activity_compare_date.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section use-trigger-date">
    <div class="label">{$form.use_trigger_date.label}</div>
    <div class="content use-trigger-date">{$form.use_trigger_date.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section use-action-date">
    <div class="label">{$form.use_action_date.label}</div>
    <div class="content use-action-date">{$form.use_action_date.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-from-date">
    <div class="label">{$form.activity_from_date.label}</div>
    <div class="content activity-date-from">{$form.activity_from_date.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-to-date">
    <div class="label">{$form.activity_to_date.label}</div>
    <div class="content activity-date-to">{$form.activity_to_date.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{literal}
  <script type="text/javascript">
    cj(document).ready(function() {
      var selectedOperator = cj('.operator').find(":selected").text();
      if (selectedOperator === 'between') {
        cj('.activity-compare-date').hide();
        cj('.use-trigger-date').hide();
        cj('.use-action-date').hide();
      }
      else {
        cj('.activity-from-date').hide();
        cj('.activity-to-date').hide();
      }
    });

    function checkOperator() {
      var selectedOperator = cj('.operator').find(":selected").text();
      if (selectedOperator === 'between') {
        cj('.activity-compare-date').hide();
        cj('.use-trigger-date').hide();
        cj('.use-action-date').hide();
        cj('.activity-from-date').show();
        cj('.activity-to-date').show();
      }
      else {
        cj('.activity-from-date').hide();
        cj('.activity-to-date').hide();
        cj('.activity-compare-date').show();
        cj('.use-trigger-date').show();
        cj('.use-action-date').show();
      }
    }
  </script>
{/literal}