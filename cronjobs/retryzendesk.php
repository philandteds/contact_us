<?php

$cli = eZCLI::instance();
if (!$cli->isQuiet()) {
    $cli->notice("Starting Zendesk resends");
}

$retries = ptZendesk::fetchInRetry();
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
