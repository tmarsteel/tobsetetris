<?php
namespace cryptmail\core;

use \cryptmail\sql\Database;
use \cryptmail\general\ErrorPage;
use \cryptmail\core\device\Browser;
use \Smarty;

/**
 * @author Tobias Marstaller
 * @desc Contains all boilerplate-code for page initialisation and deinitialisation
 */
abstract class Starter
{
	public static $SMARTY = null;

	public static function init($DB_REQUIRED = FALSE, $ADM_INTF = FALSE)
	{
            global $_CONFIG;

            Browser::load();

            header("Content-Type: text/html; charset=utf-8");

            Language::init();

            // redirect language-specific
            if (!isset($_GET["lang"]) && $_CONFIG["language"]["redirect"])
            {
                $p = isset($_GET["p"])? $_GET["p"] : "";
                if (!empty($p) && !preg_match("!^[\w|\d|\_|\-]+$!", $p))
                {
                    $p = "";
                }
                header("Location: " . $_CONFIG["webroot"] . "/" . Language::getCurrentLanguage() . "/" . \htmlspecialchars($p));
                exit();
            }

            ob_start();

            // if cryptmail-web is "locked", cancel the request with an errorpage
            if (!$ADM_INTF && $_CONFIG["maintenance"] === TRUE)
            {
                $eP = new ErrorPage(ErrorPage::ERR_MAINTENANCE);
                $eP->show();
            }

            // set error_reporting according to the debug_mode setting
            if ($_CONFIG["debug_mode"])
            {
                error_reporting(E_ALL);
            }
            else
            {
                error_reporting(E_ERROR);
            }

            // start the session
            session_start();


            try
            {
                // init Smarty
                self::$SMARTY = self::getNewSmartyIsolate();

                // Databaseconenction
                if ($DB_REQUIRED)
                {
                    Database::establishConnection();
                }
            }
            catch (\Exception $ex)
            {
                Logfile::logException($ex);
                throw $ex;
            }
	}

	public static function deinit()
	{
            $output = ob_get_contents();
            ob_end_clean();
            echo utf8_encode(utf8_decode($output));
            try {
                Database::closeConnection();
            }
            catch (\Exception $ex)
            {
                Logfile::logException($ex);
            }
	}

	public static function getNewSmartyIsolate()
	{
            global $_CONFIG;
            $smarty=new Smarty();
            $smarty->debugging = $_CONFIG["smarty"]["debug"];
            $smarty->caching = $_CONFIG["smarty"]["cache"];
            $smarty->cache_lifetime = $_CONFIG["smarty"]["cache_lifetime"];
            $smarty->setCacheDir($_CONFIG["smarty"]["cache_dir"]);

            $smarty->assign("styles", array());
            $smarty->assign("scripts", array());

            $smarty->assign("webroot", $_CONFIG["webroot"] . "/");
            $smarty->assign("browser", array(
                "osType" => Browser::$OS_TYPE,
                "isMobile" => Browser::$IS_MOBILE,
                "isTablet" => Browser::$IS_TABLET,
                "software" => Browser::$BROWSER,
                "version" => Browser::$VERSION
            ));
            if (isset($_SERVER["HTTP_REFERER"]) &&
                substr($_SERVER["HTTP_REFERER"], 0, strlen($_CONFIG["webroot"])) == $_CONFIG["webroot"])
            {
                $ref = substr($_SERVER["HTTP_REFERER"], strlen($_CONFIG["webroot"]));
                if ($ref[0] == "/")
                {
                    $ref = substr($ref, 1);
                }
                if (substr($ref, 0, 2) == Language::getCurrentLanguage())
                {
                    $ref = substr($ref, 3);
                }
                $smarty->assign("HTTP_REFERER", $ref);
            }
            else
            {
                $smarty->assign("HTTP_REFERER", "");
            }

            Language::includeInto($smarty);

            return $smarty;
	}
}
?>