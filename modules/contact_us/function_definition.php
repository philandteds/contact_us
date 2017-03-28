<?php

$FunctionList = array();
$FunctionList['zendesk_status'] = array(
    'name'             => 'zendesk_status',
    'call_method'      => array(
        'class'  => 'ContactUsFetchFunctions',
        'method' => 'fetchZendeskStatus'
    ),
    'parameter_type'   => 'standard',
    'parameters'       => array(
        array(
            'name'     => 'informationcollection_id',
            'type'     => 'int',
            'required' => true
        )
    )
);

