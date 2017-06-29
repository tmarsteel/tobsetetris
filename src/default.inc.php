<?php
include dirname(__FILE__) . "/config.inc.php";

function __autoload($class)
{
    $filePre = str_replace('\\', '/', dirname(__FILE__).'/'.$class);

    if (file_exists($filePre . '.php')) {
        include_once($filePre . '.php');
    }
    if (file_exists($filePre.'.class.php'))
    {
        include_once($filePre.'.class.php');
    }
    else if (file_exists($filePre.'.interface.php'))
    {
        include_once($filePre.'.interface.php');
    }
}
?>
