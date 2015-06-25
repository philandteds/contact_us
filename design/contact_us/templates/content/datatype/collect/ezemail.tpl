{* override template *}
{default attribute_base=ContentObjectAttribute}
{let data_text=cond( is_set( $#collection_attributes[$attribute.id] ), $#collection_attributes[$attribute.id].data_text, $attribute.content )}
<input class="box" type="text" size="20" name="{$attribute_base}_data_text_{$attribute.id}" value="{$data_text|wash( xhtml )}" placeholder="{$attribute.contentclass_attribute_name}{if $attribute.contentclass_attribute.is_required|eq(1)} *{/if}" data-validation="{if $attribute.contentclass_attribute.is_required|eq(1)}required{/if}" />
{/let}
{/default}