<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-lives_in_country">
  <div class="help-block" id="help">
    {ts}
      <p>On this form you can determine how to check if the contact lives in a country.</p>
      <p>Default behaviour is to check if the country of the <strong>primary</strong> address of the contact in question is correct. If a contact has no address or the country of the address is empty, it is assumed the contact lives in the country that you have specified as the default CiviCRM country (check <strong>Administer>Localization>Languages, Currencies, Locations</strong>).</p>
      <p>You can deviate from this behaviour by specifying a <strong>specific</strong> location type of which the country should be checked</p>
      <p>You can also tick boxes which determine what will be done if:
        <ol>
          <li>there is no address of the contact</li>
          <li>there is no country in the address of the contact</li>
        </ol>
      </p>
      <p>In this form you can also specify which country or countries the contact should live in for the condition to be true.</p>
    {/ts}
  </div>
  <div class="crm-section country-section">
    <div class="label">
      <label for="country-select">{$form.country_id.label}</label>
    </div>
    <div class="content crm-select-container" id="country_id_block">
      {$form.country_id.html}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section location_type-section">
    <div class="label">
      <label for="location_type-select">{$form.location_type_id.label}</label>
    </div>
    <div class="content crm-select-container" id="location_type_block">
      {$form.location_type_id.html}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section no_address_found-section">
    <div class="label">
      {$form.no_address_found.label}
    </div>
    <div class="content crm-form-checkbox">
      {$form.no_address_found.html}
      <div class="clear"></div>
    </div>
  </div>
  <div class="crm-section no_country_found-section">
    <div class="label">
      {$form.no_country_found.label}
    </div>
    <div class="content crm-form-checkbox">
      {$form.no_country_found.html}
      <div class="clear"></div>
    </div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>