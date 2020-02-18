<div class="crm-content-block crm-block">
  </div>
  <div class="action-link">
    <a class="button new-option" href="{$add_url}">
      <span><div class="icon add-icon ui-icon-circle-plus civirule-add-new"></div>{ts}Add CiviRule{/ts}</span>
    </a>
  </div>
  <div id="civirule_wrapper" class="dataTables_wrapper">
    {include file="CRM/common/jsortable.tpl"}
    {include file="CRM/common/enableDisableApi.tpl"}
    <table id="civirule-table" class="display">
      <thead>
        <tr>
          <th id="sortable">{ts}Rule Label{/ts}</th>
          <th id="sortable">{ts}Trigger{/ts}</th>
          <th id="sortable">{ts}Tag(s){/ts}</th>
          <th id="nosort">{ts}Description{/ts}</th>
          <th id="sortable">{ts}Active?{/ts}</th>
          <th id="sortable">{ts}Date Created{/ts}</th>
          <th id="sortable">{ts}Created By{/ts}</th>
          <th id="nosort"></th>
        </tr>
      </thead>
      <tbody>
        {assign var="row_class" value="odd-row"}
        {foreach from=$rules key=rule_id item=row}
          <tr id="row_{$row.id}" class="crm-entity {cycle values="odd-row,even-row"}{if NOT $row.is_active} disabled{/if}">
            <td hidden="1">{$row.id}</td>
            <td>{$row.label}</td>
            <td>{$row.trigger_label}</td>
            <td>{$row.tags}</td>
            <td>{$row.description}
              {if (!empty($row.help_text))}
                <a id="civirule_help_text_icon" class="crm-popup medium-popup helpicon" onclick="showRuleHelp({$row.id})" href="#"></a>
              {/if}
            <td>{$row.is_active}</td>
            </td>
            <td>{$row.created_date|crmDate}</td>
            <td>{$row.created_contact_name}</td>
            <td>
              <span>
                {foreach from=$row.actions item=action_link}
                  {$action_link}
                {/foreach}
              </span>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>
  <div class="action-link">
    <a class="button new-option" href="{$add_url}">
      <span><div class="icon add-icon ui-icon-circle-plus civirule-add-new"></div>{ts}Add CiviRule{/ts}</span>
    </a>
  </div>
</div>

{literal}
  <script>
    function showRuleHelp(ruleId) {
      console.log('rule id is ' + ruleId);
      CRM.api3('CiviRuleRule', 'getsingle', {"id": ruleId})
          .done(function(result) {
            cj("#civirule_helptext_dialog-block").dialog({
              width: 600,
              height: 300,
              title: "Help for Rule " + result.label,
              buttons: {
                "Done": function() {
                  cj(this).dialog("close");
                }
              }
            });
            cj("#civirule_helptext_dialog-block").html(result.help_text);
          });
    }

    function civiruleEnableDisable(ruleId, action) {
      if (action === 1) {
        CRM.api3('CiviRuleRule', 'getClones', {"id": ruleId})
            .done(function (result) {
              if (result.count > 0) {
                location.href = CRM.url('civicrm/civirule/form/ruleenable', {"id": ruleId});
              }
              else {
                CRM.api3('CiviRuleRule', 'create', {"id": ruleId, "is_active": action})
                    .done(function (result) {
                      location.reload(true);
                    });
              }
            });
      }
      else {
        CRM.api3('CiviRuleRule', 'create', {"id": ruleId, "is_active": action})
            .done(function (result) {
              location.reload(true);
            });
      }
    }
  </script>
{/literal}


