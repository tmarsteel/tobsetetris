<?php
namespace cryptmail\core\device;

class Browser
{
    public static $OS_TYPE = "UNKNOWN";
    public static $PREF_LANG = "en";
    public static $LANGUAGES = array();
    public static $IS_MOBILE = false; // is Phone or Reader?
    public static $IS_TABLET = false; // is Tablet?
    public static $BROWSER = "UNKNOWN";
    public static $VERSION = "UNKNOWN";

    static function load($useragent = null)
    {
        if ($useragent == null)
        {
            if (isset($_SERVER["HTTP_USER_AGENT"]))
            {
                $useragent = $_SERVER["HTTP_USER_AGENT"];
            }
            else
            {
                return;
            }
        }

        // OS-Type
        if (ini_get("browscap"))
        {
            $browser = get_browser($useragent);
            self::$BROWSER = $browser->browser;
            self::$VERSION = floatval($browser->version);
            $os = strToLower($browser->platform);
            if (substr($os, 0, 3) == "win")
            {
                self::$OS_TYPE = "WIN";
            }
            else if (substr($os, strlen($os) - 3) == "nix" || substr($os, strlen($os) - 3) == "nux")
            {
                self::$OS_TYPE = "UNIX";
            }
            else
            {
                $ua = strToLower($useragent);
                if (strpos($ua, "windows") !== false)
                {
                    self::$OS_TYPE = "WIN";
                }
            }
        }

        // Language
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
        {
            $langs = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            foreach ($langs as &$l)
            {
                $l = substr(trim($l), 0, 2);
            }
            self::$PREF_LANG = $langs[0];
            self::$LANGUAGES = $langs;
        }

        $detector = new Detector(null, $useragent);

        // device type
        self::$IS_TABLET = $detector->isTablet();
        self::$IS_MOBILE = self::$IS_TABLET? false : $detector->isMobile();
    }
}