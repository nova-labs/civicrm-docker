{* https://civicrm.org/licensing *}

<div class="crm-block crm-content-block">
    {if $isAllOk}
      <div class="alert alert-success">{$intro}</div>
    {/if}
    {if $shouldOfferToFix}
      <div class="alert alert-warning status crm-warning">
        <h3>Problems discovered:</h3>
        <ul>
            {foreach from=$messages item=message}
              <li>{$message.title}: {$message.message}</li>
            {/foreach}
        </ul>
      </div>
      <div class="alert alert-info status help">
        <p>Please click the Update / Create webhook button to
          attempt to fix.</p>
        <p>This will attempt to check and correct your Stripe webhooks. Note: do not
          run this in a development environment unless you want a webhook set up that
          points to your development domain(!).</p>
      </div>
    {/if}

    {if $isStillBad}
      <div class="alert alert-danger status crm-error">
        <h3>There were errors updating the webhook(s):</h3>
        <ul>
            {foreach from=$messages item=message}
              <li>{$message.title}: {$message.message}</li>
            {/foreach}
        </ul>
      </div>
      <div class="alert alert-info status help">
        The easiest way to fix this, is to
        delete your webhooks from your Stripe account(s) and then revisit this page
        to recreate them correctly.
      </div>
    {/if}
    {* FOOTER *}
    {if $shouldOfferToFix || $isStillBad}
      <div class="crm-submit-buttons">
          {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    {/if}
</div>
