<?php

/**
 * Persistent object to map to pt_trash.
 */
class ptZendesk extends eZPersistentObject
{
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
}