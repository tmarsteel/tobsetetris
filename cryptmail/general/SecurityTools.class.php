<?php
namespace cryptmail\general;

use \cryptmail\sql\Database;

/**
 * @desc Contains all security-related functions 
 */
abstract class SecurityTools
{
	public static function escapeString($str)
	{
		return Database::escapeString($str);
	}
	
	public static function secureHash($str)
	{
		return sha512(md5($str)."lfs1�#2");
	}
    
    public static function prettifyPath($path)
    {
        $rev_strpos = function($haystack, $needle, $offset=0) {
            $haystack = strrev($haystack);
            $needle = strrev($needle);
            if ($offset!=0) $offset = strlen($haystack)-$offset;
            $pos = strpos($haystack, $needle, $offset);
            if ($pos === false) return false;
            return strlen($haystack) - strlen($needle) - $pos;
        };
        $path = str_replace("\\", "/", $path);
        $pos = strpos($path, "/../");
        while ($pos != false) {
            $last = $rev_strpos($path, "/", $pos - 1);
            $path = substr($path, 0, $last + 1).substr($path, $pos + 4);
            $pos = strpos($path, "/../");
        }
        return $path;
    }
}
?>