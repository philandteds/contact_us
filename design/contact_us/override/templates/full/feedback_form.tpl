{ezscript_load(
    array(
        'jquery.fancybox.pack.js',
        'feedback_form.js'
    )
)}
{ezcss_load(
    array(
        'feedback_form.css'
    )
)}


{def
    $query_types = fetch(
        'content', 'list',
        hash(
            'parent_node_id', 1,
            'depth', false(),
            'class_filter_type', 'include',
            'class_filter_array', array( 'query_type' ),
            'main_node_only', true(),
            'ignore_visibility', true()
        )
    )
    $countries      = fetch( 'content', 'country_list' )
    $button_url     = false()
    $button_text    = false()
    $attribute      = false()
    $selected_value = false()
}

{include uri="design:parts/meta.tpl"}

<h1 class="reg_heading dotted-line">{$node.data_map.title.content|wash()}</h1>

{if $node.data_map.description.content.is_empty|not()}
    <div class="attribute-short">
        {attribute_view_gui attribute=$node.data_map.description}
    </div>
{/if}

{if and(
    is_set( $validation ),
    is_set( $collection_attributes )
)}
    {include
        name=Validation
        uri='design:content/collectedinfo_validation.tpl'
        class='message-warning'
        validation=$validation
        collection_attributes=$collection_attributes
    }
{/if}
<div>
<form method="post" class="registration feedback" action="{'content/action'|ezurl('no')}">
    <h2>{'Your query'|i18n('philandteds')}</h2>
    <div class="query reg_wrapper">
        {set $attribute = $node.data_map.type_of_query}
        
        {* <label>
            {$attribute.contentclass_attribute_name}:{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$attribute html_class="hidden query_type"}
        </label> *}
        <select class="query_type">
            {if is_set($collection_attributes[$attribute.id])}
                {set $selected_value = $collection_attributes[$attribute.id].data_text}
            {/if}
            <option value="0" data-id="query_type_0">{'Type of query'|i18n( 'extension/contact_us' )}</option>
            {foreach $query_types as $type}
                <option data-id="#query_type_{$type.node_id}" value="{$type.name|wash()}" {if $selected_value|eq($type.name|wash())} selected{/if}>
                    {$type.name|wash()}
                </option>
            {/foreach}
        </select>
        <a href="#" class="hidden fancybox-link fancybox"></a>
        {foreach $query_types as $type}
            {set
                $button_url  = '#'
                $button_text = 'OK'
            }
            {if $type.data_map.help_text.has_content}
                <div id="query_type_{$type.node_id}" class="help_text">
                    <div class="description">
                        {attribute_view_gui attribute=$type.data_map.help_text}
                    </div>
                    {if or(
                        $type.data_map.ok_link_internal.has_content,
                        $type.data_map.ok_link_external.has_content
                    )}
                        {if $type.data_map.ok_link_internal.has_content}
                            {set
                                $button_url  = $type.data_map.ok_link_internal.content.main_node.url_alias|ezutl( 'no' )
                                $button_text = $type.data_map.ok_link_internal.content.main_node.name|wash()
                            }
                        {elseif $type.data_map.ok_link_external.has_content}
                            {set
                                $button_url  = $type.data_map.ok_link_external.content
                                $button_text = $type.data_map.ok_link_external.data_text|wash()
                            }
                        {/if}
                    {/if}
                    <div class="buttons">
                        <a class="ok" href="{$button_url}">{$button_text}</a>
                        <a href="#" class="cancel">{'Cancel'|i18n('philandteds')}</a>
                    </div>
                </div>
            {/if}
        {/foreach}
        {set $attribute = $node.data_map.subject}
      {*<label>{$attribute.contentclass_attribute_name}:{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if}</label>*} 
            {attribute_view_gui attribute=$attribute}
        
        {set $attribute = $node.data_map.message}
        {*<label>{$attribute.contentclass_attribute_name}:{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if}</label>*}
            {attribute_view_gui attribute=$attribute}
        
    </div>
    
    <h2>{'Your details'|i18n('philandteds')}</h2>
    <div class="details reg_wrapper">
        {set $attribute = $node.data_map.country}
        {*<label>{$attribute.contentclass_attribute_name}:{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if}</label>*}
            {attribute_view_gui attribute=$attribute html_class="hidden country"}
        
        <select class="country">
            {if is_set($collection_attributes[$attribute.id])}
                {set $selected_value = $collection_attributes[$attribute.id].data_text}
            {/if}
            <option value="0">{'Please select country'|i18n( 'extension/contact_us' )}</option>
            {foreach $countries as $country}
                <option value="{$country.Alpha3}"{if $selected_value|eq($country.Alpha3)} selected{/if}>{$country.Name|wash()}</option>
            {/foreach}
        </select>
        {set $attribute = $node.data_map.first_name}
        {*<label>{$attribute.contentclass_attribute_name}:{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if} </label>*}
            {attribute_view_gui attribute=$attribute}
       
        {set $attribute = $node.data_map.email}
        {*<label>{$attribute.contentclass_attribute_name}:{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if}</label>*}
            {attribute_view_gui attribute=$attribute}
        
        {set $attribute = $node.data_map.phone}
        {*<label>{$attribute.contentclass_attribute_name}:{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if} </label>*}
            {attribute_view_gui attribute=$attribute}
       
        <label>
            {attribute_view_gui attribute=$node.data_map.zendesk_ticket_id html_class="hidden zendesk_ticket_id" default_value=""}
        </label>
        <label>
            {attribute_view_gui attribute=$node.data_map.email_sent html_class="hidden email_sent"}
        </label>
        <div class="capctha">
        {attribute_view_gui attribute=$node.data_map.captcha}
    </div>
    </div>
    

    <div class="attribute-short">
        <input type="submit" name="ActionCollectInformation" class="continue-btn" value="{'Send'|i18n('philandteds')}" />
        <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
        <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
        <input type="hidden" name="ViewMode" value="full" />
    </div>
</form>
</div>

<script>
{literal}
head(function(){
	$.validate();
    });
{/literal}
</script>


{undef $query_types $countries $button_url $button_text $attribute $selected_value}