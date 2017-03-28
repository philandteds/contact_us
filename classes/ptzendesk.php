<?php

/**
 * Persistent object to map to pt_trash.
 */
class ptZendesk extends eZPersistentObject
{
    const ZENDESK_RETRY_STATUS_FAIL     = 'FAIL';    // maximum number of retries exceeded.
    const ZENDESK_RETRY_STATUS_SUCCESS  = 'SUCCESS'; // successfully sent to zendesk
    const ZENDESK_RETRY_STATUS_RETRY    = 'RETRY';   // prior send attempt failed, retrying

    const ZENDESK_RETRY_STATUS_PENDING  = 'PENDING'; // prior to the first attempt to call the API

    /**
     * Schema definition
     * eZPersistentObject implementation for ezsite_data table
     * @see kernel/classes/ezpersistentobject.php
     * @return array
     */
    public static function definition()
    {
        return array('fields' =>
            array('informationcollection_id' => array('name' => 'informationcollection_id',
                'datatype' => 'integer',
                'default' => null,
                'required' => true),

                'retry_count' => array('name' => 'retry_count',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true),

                'status' => array('name' => 'status',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true),

                'error' => array('name' => 'error',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true)
            ),

            'keys' => array('informationcollection_id'),
            'class_name' => 'ptZendesk',
            'name' => 'pt_zendesk',
            'function_attributes' => array()
        );
    }

    static function fetch( $collection_id, $asObject = true )
    {
        if ( !$collection_id )
            return null;

        return eZPersistentObject::fetchObject( ptZendesk::definition(),
            null,
            array( 'informationcollection_id' => $collection_id),
            $asObject );
    }

    static function fetchInRetry( )
    {
        return eZPersistentObject::fetchObjectList( ptZendesk::definition(),
            null,
            array( 'status' => self::ZENDESK_RETRY_STATUS_RETRY),
            true );
    }


    static function markAsSuccessful( $collection_id ) {

        $collection = ptZendesk::fetch($collection_id);
        if (!$collection) {
            return false;
        }

        $collection->setAttribute('status', self::ZENDESK_RETRY_STATUS_SUCCESS);
        $collection->setAttribute( 'error', null);
        $collection->store();
    }

}