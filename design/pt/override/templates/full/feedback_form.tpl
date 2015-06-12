{ezscript_load( array('feedback_form.js'))}
{ezcss_load( array('feedback_form.css'))}
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
    $countries   = fetch( 'content', 'country_list' )
    $button_url  = false()
    $button_text = false()
}

{include uri="design:parts/meta.tpl"}

<h1>{$node.data_map.title.content|wash()}</h1>

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

<form method="post" action="{'content/action'|ezurl('no')}">
    <div class="query">
        {'Your query'|i18n('philandteds')}
        <label>
            {$node.data_map.type_of_query.contentclass_attribute_name}:{if $node.data_map.type_of_query.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$node.data_map.type_of_query html_class="hidden query_type"}
        </label>
        <select class="query_type">
            <option value="0" data-id="query_type_0"></option>
            {foreach $query_types as $type}
                <option data-id="query_type_{$type.node_id}" value="{$type.name|wash()}">{$type.name|wash()}</option>
            {/foreach}
        </select>
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
        <label>
            {$node.data_map.subject.contentclass_attribute_name}:{if $node.data_map.subject.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$node.data_map.subject}
        </label>
        <label>
            {$node.data_map.message.contentclass_attribute_name}:{if $node.data_map.message.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$node.data_map.message}
        </label>
    </div>
    <div class="details">
        {'Your details'|i18n('philandteds')}
        <label>
            {$node.data_map.country.contentclass_attribute_name}:{if $node.data_map.country.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$node.data_map.country html_class="hidden country"}
        </label>
        <select class="country">
            <option value="0"></option>
            {foreach $countries as $country}
                <option value="{$country.Alpha3}">{$country.Name|wash()}</option>
            {/foreach}
        </select>
        <label>
            {$node.data_map.first_name.contentclass_attribute_name}:{if $node.data_map.first_name.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$node.data_map.first_name}
        </label>
        <label>
            {$node.data_map.email.contentclass_attribute_name}:{if $node.data_map.email.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$node.data_map.email}
        </label>
        <label>
            {$node.data_map.phone.contentclass_attribute_name}:{if $node.data_map.phone.contentclass_attribute.is_required|eq(1)} *{/if}
            {attribute_view_gui attribute=$node.data_map.phone}
        </label>
    </div>
    <div class="capctha">
        {attribute_view_gui attribute=$node.data_map.captcha}
    </div>

    <div class="attribute-short">
        <input type="submit" name="ActionCollectInformation" class="" value="{'Send'|i18n('philandteds')}" />
        <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
        <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
        <input type="hidden" name="ViewMode" value="full" />
    </div>
</form>
{undef $query_types $countries $button_url $button_text}