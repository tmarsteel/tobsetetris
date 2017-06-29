<?php
$name_regex = "/(\w|\d|_|-|\.)+/";
$value_regex = "/.*/";

session_start();

if (!isset($_SESSION["settings"]))
{
    $_SESSION["settings"] = array();
}

if (isset($_POST["name"]) && preg_match($name_regex, $_POST["name"]))
{
    $name = $_POST["name"];
    if (isset($_POST["value"]) && preg_match($value_regex, $_POST["value"]))
    {
        $value = $_POST["value"];
        
        $_SESSION["settings"][$name] = $value;
    }
    else
    {
        if (isset($_SESSION["settings"]["value"]))
        {
            echo $_SESSION["settings"][$name];
        }
    }
}

?>