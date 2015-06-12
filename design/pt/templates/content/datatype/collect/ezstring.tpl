{default
    attribute_base = 'ContentObjectAttribute'
    html_class     = 'full'
    data_text      = false()
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
    name="{$attribute_base}_ezstring_data_text_{$attribute.id}"
    value="{$data_text|wash( xhtml )}"
/>
{/default}