<?php
$Module = array( 'name' => 'Contact Us',
    'variable_params' => true );

$ViewList = array();
$ViewList['resend_zendesk'] = array(
    'script' => 'resendtozendesk.php',
    'params' => array('InformationCollectionID'),
    'unordered_params' => array('offset' => 'Offset'),
    'functions' => array('read')
);

$FunctionList = array(
    'read' => array()
);
