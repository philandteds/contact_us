<?php

class ContactUsFetchFunctions
{
    public static function fetchZendeskStatus( $informationcollection_id ) {

        $zendesk_status = ptZendesk::fetch($informationcollection_id);
        if ($zendesk_status == null) {
            $zendesk_status = false;
        }

        return array (
            'result' => $zendesk_status
        );
    }
}