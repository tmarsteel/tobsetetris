<?php
include dirname(__FILE__)."/config.inc.php";

function __autoload($class)
{
	    $filePre = str_replace('\\', '/', dirname(__FILE__).'/../'.$class);
    if (file_exists($filePre.'.class.php'))
    {
        include($filePre.'.class.php');
    }
    else if (file_exists($filePre.'.interface.php'))
    {
        include($filePre.'.interface.php');
    }
}
?>
