<?php

$informationCollectionId = $Params['InformationCollectionID'];
$offset = $Params['Offset'];

$error = false;

try {
    $collection = eZInformationCollection::fetch($informationCollectionId);

    $trackingRecord = ptZendesk::fetch($informationCollectionId);
    if ($trackingRecord->attribute('status') == ptZendesk::ZENDESK_RETRY_STATUS_SUCCESS) {
        throw new Exception('Zendesk ticket has already been successfully raised. No need to do it again.');
    }

    EventListenerHelper::sendCollectedInfoToZD($collection, EventListenerHelper::buildCollectedInfoArray($collection));

    // if the send has worked, reset the status to SUCCESS
    ptZendesk::markAsSuccessful($informationCollectionId);

} catch (Exception $e) {
    $error = $e->getMessage();
}

$tpl = eZTemplate::factory();

$tpl->setVariable('offset', $offset);
$tpl->setVariable('contentobject_id', $collection->attribute('contentobject_id'));
$tpl->setVariable( 'information_collection_id', $informationCollectionId );
$tpl->setVariable( 'error', $error);

$Result = array();
$Result['content'] = $tpl->fetch( 'design:zendesk/resend.tpl' );
