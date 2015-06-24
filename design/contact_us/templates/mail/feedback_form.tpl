{"Hi there,"|i18n('contact_us/mail')}

{"A new question was submitted by a customer - please reply to them, using the details below."|i18n('contact_us/mail')}

=================================================

{foreach $collected_info as $identifier => $info}
{if array('zendesk_ticket_id','subject')|contains($identifier)|not}
    {$info.name}:  {$info.value}{array('0013')|chr()}{array('0013')|chr()}
{/if}
{/foreach}

=================================================

{"Many thanks,"|i18n('contact_us/mail')}

phil&teds | mountain buggy
