<?php
namespace cryptmail\core;

use \cryptmail\core\device\Browser;

/**
 * @author Tobias Marstaller
 * @desc Manages all Language-related jobs
 */
class Language
{
    // index 0 is the default language
    private static $supportedLangs = array("en", "de");
    protected static $lang = null;
    protected static $langData = array();

    public static function init()
    {
        // check the url for language settings
        if (isset($_GET["lang"]) && in_array($_GET["lang"], self::$supportedLangs))
        {
            self::$lang = $_GET["lang"];
        }
        // check the browsers language settings
        else if (Browser::$PREF_LANG != null)
        {
            foreach (Browser::$LANGUAGES as $l)
            {
                if (in_array($l, self::$supportedLangs))
                {
                    self::$lang = $l;
                    break;
                }
            }
        }
        if (self::$lang == null)
        {
            self::$lang = self::$supportedLangs[0];
        }
        // self::$lang = "en";
        self::load('common');
    }
    public static function getCurrentLanguage()
    {
        return self::$lang;
    }

    public static function load($file, $path = null)
    {
        global $_CONFIG;
        $lines = file($_CONFIG["language"]["directory"] . '/' . self::$lang . '/' .
            $file . '.lang');
        if ($lines == FALSE)
        {
            return;
        }
        $path = $path == null? explode("/", $file) : explode(".", $path);
        $target = &self::$langData;
        for ($i = 0;$i < count($path);$i++)
        {
            $key = $path[$i];
            if (!isset($target[$key]))
            {
                $target[$key] = array();
            }
            $target = &$target[$key];
        }
        foreach ($lines as $line)
        {
            $line = trim($line);
            if (empty($line))
            {
                continue;
            }
            $pos = strpos($line, ":");
            if ($pos == -1)
            {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $target[$key] = $value;
        }
    }

    public static function get($key)
    {
        $key = explode(".", $key);
        $target = &self::$langData;
        $str = null;
        foreach ($key as $k)
        {
            if (!isset($target[$k]))
            {
                return null;
            }
            if (is_array($target[$k]))
            {
                $target = &$target[$k];
            }
            else
            {
                $str = $target[$k];
                break;
            }
        }
        if ($str == null)
        {
            return null;
        }
        $args = func_get_args();
        $j = count($args) - 1;
        for ($i = 1;$i < $j;$i++)
        {
            $str = str_replace('$$' . $i, $args[$i], $str);
        }
        return $str;
    }

    public static function includeInto(\Smarty $smarty)
    {
        $ar = self::$langData;
        $ar["shortStr"] = self::$lang;
        $smarty->assign("language", $ar);
    }
}
?>