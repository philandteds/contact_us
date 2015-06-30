{* override template *}
{default attribute_base='ContentObjectAttribute' html_class='full'}
{let data_text=cond( is_set( $#collection_attributes[$attribute.id] ), $#collection_attributes[$attribute.id].data_text, $attribute.content )}
<textarea class="{eq( $html_class, 'half' )|choose( 'box', 'halfbox' )}" name="{$attribute_base}_data_text_{$attribute.id}" cols="70" rows="{$attribute.contentclass_attribute.data_int1}" placeholder="{$attribute.contentclass_attribute_name}{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if}" data-validation="{if $attribute.contentclass_attribute.is_required|eq(1)}required{/if}" data-validation-error-msg="{if $attribute.contentclass_attribute.is_required|eq(1)}Please add required information to the text box{/if}">{$data_text|wash}</textarea>
{/let}
{/default}