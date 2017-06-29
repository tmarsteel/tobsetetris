<?php
namespace cryptmail\general;

use \cryptmail\core\Language;
use \cryptmail\core\Starter;
use \cryptmail\core\Logfile;
use \cryptmail\core\device\Browser;

/**
 * @author Tobias Marstaller
 * @desc Constructs a default error page for all kinds of errors
 */
class ErrorPage
{

    // If this is set to true, the errous queries qill be printed out on the webpage
    // so switch this to FALSE whenever the code is in contact with the public
    const DEBUG_SQL = FALSE;

    const ERR_EXCEPTION = -1;
    const ERR_NOTFOUND = 404;
    const ERR_NOPERM = 403;
    const ERR_SERVERERROR = 500;
    const ERR_MAINTENANCE = -2;

    /**
     * @param -string[] properties List of all error-page specific elements
     * @param -string type The type of error that occured, see the ini files in /errorpages
     * @param -string[] The data from the ini file related to this error-type. By default data for 500 in english
     */
    private $properties = array();
    private $type;
    private $iniData = array(
        "status" => "500 Internal Server Error",
        "title" => "Servererror",
        "headline" => "There are a few technical difficulties.",
        "message" => "Unfortunately there are technical difficulties on our server. Please check back in five minutes."
    );

    public function __construct($error = null)
    {
        if ($error instanceof \Exception)
        {
            $this->type = "exception";
            $this->properties["EX_CLASS"] = get_class($error);
            $this->properties["EX_MESSAGE"] = $error->getMessage();
            // If this is a errous query
            if ($error instanceof cryptmail\sql\MySqlQueryException)
            {
                $this->type = ErrorPage::DEBUG_SQL? "database_debug" : "database";
                $this->properties["EX_QUERY"] = $error->getErrousCommand();
            }
        }
        else
        {
            switch ($error)
            {
                case ErrorPage::ERR_NOTFOUND:
                    $this->type = "notfound_404";
                break;
                case ErrorPage::ERR_NOPERM:
                    $this->type = "noperm_403";
                break;
                case ErrorPage::ERR_MAINTENANCE:
                    $this->type = "maintenance";
                break;
                case ErrorPage::ERR_SERVERERROR:
                    $this->type = "servererror_500";
                break;
            }
        }
        $file = dirname(__FILE__).'/../../errorpages/'.Language::getCurrentLanguage()
            .'/'.$this->type.'.ini';

        if (!file_exists($file))
        {
            Logfile::logError('Error #'.$this->type.' occured but the ini-file in '.
            Language::getCurrentLanguage().' cannot be found. Error 500 is sent.');
        }
        else
        {
            @$this->iniData = parse_ini_file($file);
            if ($this->iniData == null)
            {
                Logfile::logError('Error #'.$this->type.' has a malformed ini-file '.
                    'for language '.Language::getCurrentLanguage().'; empty output!');
                Logfile::appendLog('PHP-Error: '.$php_errormsg);
            }
        }
    }

    public function setParam($key, $value)
    {
        $this->properties[strToUpper($key)] = $value;
    }

    /**
     * @desc Sends the errorpage to the user and calls exit() if $exit is TRUE
     * @param -bool exit If true, this method will call exit() before returning
     */
    public function show($exit = true)
    {
        $smarty = Starter::getNewSmartyIsolate();

        foreach ($this->properties as $key => $value)
        {
            $smarty->assign($key, $value);
        }

        $smarty->assign("PAGE_TITLE", $this->iniData["title"]);
        $smarty->assign("err_headline", $this->iniData["headline"]);
        $smarty->assign("err_message", $this->iniData["message"]);
        $smarty->assign("styles", array("subpage.css", "errorpage.css"));

        header("HTTP/1.1 ".$this->iniData["status"]);

        if (Browser::$IS_MOBILE)
        {
            $smarty->display(dirname(__FILE__).'/../../templates/mobile/errorpage.tpl');
        }
        else
        {
            $smarty->display(dirname(__FILE__).'/../../templates/desktop/errorpage.tpl');
        }

        if ($exit)
        {
            Starter::deinit();
            exit();
        }
    }
}
?>