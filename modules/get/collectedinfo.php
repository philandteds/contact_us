<?php

$http   = eZHTTPTool::instance();
$module = $Params['Module'];

$collectionID = isset($Params['collectionID']) ? $Params['collectionID'] : false;

if ($collectionID){
    $collection       = eZInformationCollection::fetch($collectionID);
    $collectionObject = eZContentObject::fetch($collection->attribute('contentobject_id'));
    $attributes       = $collection->informationCollectionAttributes();

    foreach ($attributes as $key => $attribute) {
        $result[] = array(
            'name'  => $attribute->contentClassAttributeName(),
            'value' => $attribute->attribute('data_text')
        );
    }
}
