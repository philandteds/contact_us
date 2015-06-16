{def
    $collection = cond(
        $collection_id,
        fetch(
            'content', 'collected_info_collection',
            hash( collection_id, $collection_id )
        ),
        fetch(
            'content', 'collected_info_collection',
            hash( contentobject_id, $node.contentobject_id ) 
        )
    )
}

{set-block scope=global variable=title}
    {'Form %formname'|i18n( 'booking_gha/gha', , hash( '%formname', $node.name|wash() ) )}
{/set-block}
<h1>{$node.data_map.title.content|wash()}</h1>
<div class="attribute-short">
    {if $node.data_map.thank_you_message.has_content}
        {attribute_view_gui attribute=$node.data_map.thank_you_message}
    {else}
        {'Thank you for getting in touch'|i18n('extension/site')}.<br/>
    {/if}
</div>
