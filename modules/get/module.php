<?php

/**
 * @package PT
 * @date    11 Jan 2015
 * */
$Module = array(
    'name'            => 'Get',
    'variable_params' => true
);

$ViewList = array(
    'collectedinfo' => array(
        'functions' => array( 'get' ),
        'script'    => 'collectedinfo.php',
        'params'    => array('collectionID')
    )
);

$FunctionList = array(
    'get' => array()
);
