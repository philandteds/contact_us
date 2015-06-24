{"The following information was collected"|i18n("design/standard/content/edit")}:

{foreach $collected_info as $identifier => $info}
    {if and($identifier|ne('zendesk_ticket_id'), $identifier|ne('is_email_sent'))}
    {$info.name}: {$info.value}
    {/if}

{/foreach}
