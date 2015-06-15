{"The following information was collected"|i18n("design/standard/content/edit")}:

{foreach $collected_info as $identifier => $info}
    {if $identifier|ne('zendesk_ticket_id')}
    {$info.name}: {$info.value}
    {/if}

{/foreach}
