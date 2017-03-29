{* Sent to an administrator when the Zendesk ticket cannot be created after x retries. *}

We have FAILED to create a ticket in Zendesk for the following customer feedback. The API connection to Zendesk is
not working. Please have an appropriate person investigate it.

Error:       {$zendesk_error}
Admin page:  {concat('/infocollector/view/', $collection_id)|ezurl('no', 'relative')}

Content:
--------


{foreach $collected_info as $identifier => $info}
{if array('zendesk_ticket_id','subject','is_email_sent')|contains($identifier)|not}
  {$info.name}:  {$info.value}{array('0013')|chr()}{/if}
{/foreach}
