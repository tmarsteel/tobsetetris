<?php
include dirname(__FILE__)."/config.inc.php";

function __autoload($class)
{
    // Smarty Main-Class
    if ($class == 'Smarty' || $class == '\Smarty')
    {
        include(dirname(__FILE__).'/../cryptmail/core/smarty/Smarty.class.php');
        return;
    }
	
    $filePre = str_replace('\\', '/', dirname(__FILE__).'/../'.$class);
    if (file_exists($filePre.'.class.php'))
    {
        include($filePre.'.class.php');
    }
    else if (file_exists($filePre.'.interface.php'))
    {
        include($filePre.'.interface.php');
    }
	else
	{
            // Smarty sub-classes
            $class=strToLower($class);
            if (strpos($class, "smarty") !== FALSE)
            {
                $class = substr($class, strrpos($class, '\\'));

                $file = dirname(__FILE__).'/../cryptmail/core/smarty/sysplugins/'.$class.'.php';	

                if (file_exists($file))
                {
                    include($file);
                }
            }
	}
}
?>