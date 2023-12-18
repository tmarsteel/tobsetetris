<?php
$_CONFIG=array (
    'database' => array (
        'host' => 'localhost',
        'user' => 'tetris',
        'password' => 'tetris',
        'base' => 'tetris',
    ),
    'logfile' => array (
        'error' => '/var/log/tetris-error.log',
    ),
    'webroot' => '/',
);
define('TBL_PRFX', '');
ini_set("session.gc_maxlifetime", 60 * 60);
ini_set("session.cookie_lieftime", 60 * 60);
?>
