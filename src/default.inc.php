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
        if (!class_exists($class)) {
            throw new \Exception("$filePre.class.php does not define class $class");
        }
    }
    else if (file_exists($filePre.'.interface.php'))
    {
        include_once($filePre.'.interface.php');
    }
}
?>
