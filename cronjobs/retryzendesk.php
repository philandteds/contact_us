<?php

$cli = eZCLI::instance();
if (!$cli->isQuiet()) {
    $cli->notice("Starting Zendesk resends");
}

$currentUser = eZUser::currentUser();

// due to a situation where the event listener is not being triggered for every send, we may end up with
// situations where there is no ptZendesk row for a Contact Us row. This is a workaround, where we identify entries in
// collection list that have no send status (and no zendesk ticket ID), and add a PENDING row into ptZendesk to catch them.

$db = eZDB::instance();
$db->begin();

$nodeId = eZURLAliasML::fetchNodeIDByPath('Support/Contact-Us');
$object = eZContentObject::fetchByNodeID($nodeId);

if ($object instanceof eZContentObject) {
    $collectionList = eZInformationCollection::fetchCollectionsList($object->ID, false, false,
        array('limit' => 25, 'offset' => 0), array('created', false));

    foreach ($collectionList as $collection) {

        $dataMap = $collection->dataMap();
        $zendeskTicketId = $dataMap['zendesk_ticket_id']->content();

        if (!$zendeskTicketId) {
            // have found a row with no zendesk_ticket_id. Check to see if we have a ptZendesk status.
            $ptZendeskStatus = ptZendesk::fetch($collection->ID);

            if (!$ptZendeskStatus) {
                $cli->output("Found information collection: $informationCollectionId, which has no ptZendesk tracking information or Zendesk ticket ID. Creating missing ptZendesk record.");

                // we have an unknown row with no ptZendesk status and no ticket. Create a ptZendesk PENDING row so that it is picked up on this send cycle.
                $missingRow = new ptZendesk(array(
                    'informationcollection_id' => $collection->ID,
                    'retry_count' => 0,
                    'status' => ptZendesk::ZENDESK_RETRY_STATUS_PENDING
                ));
                $missingRow->store();
            }
        }
    }
}

$db->commit();


$retries = ptZendesk::fetchInPendingOrRetry();
foreach ($retries as $retry) {

    $informationCollectionId = $retry->attribute('informationcollection_id');

    if (!$cli->isQuiet()) {
        $cli->output("Processing information collection: $informationCollectionId.");
    }

    EventListenerHelper::sendCollectedInfoToZDWithRetry($informationCollectionId);
}


if (!$cli->isQuiet()) {
    $cli->notice("Finished Zendesk resends");
}
