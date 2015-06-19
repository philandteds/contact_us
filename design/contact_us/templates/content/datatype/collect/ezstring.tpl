{default
    attribute_base = 'ContentObjectAttribute'
    html_class     = 'full'
    data_text      = false()
    default_value  = false()
}
{set $data_text = cond(
    is_set( $#collection_attributes[$attribute.id] ),
    $#collection_attributes[$attribute.id].data_text,
    $attribute.content
)}
<input
    class="{$html_class}"
    type="text"
    size="70"
    id="{$attribute.contentclass_attribute_identifier|wash}"
    name="{$attribute_base}_ezstring_data_text_{$attribute.id}"
    value="{if and($default_value, $data_text|eq(''))}{$default_value}{else}{$data_text|wash( xhtml )}{/if}"
/>
{/default}
