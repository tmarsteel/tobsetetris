<?php
namespace cryptmail\core;

use \cryptmail\sql\Database;
use \cryptmail\general\user\User;

/**
 * Created on 09.08.2012
 * @author Tobias Marstaller
 */
abstract class CLIStarter extends Starter
{
    public static function init($ADM_INTF = false)
    {
        try
		{
			// init Smarty
			self::$SMARTY = self::getNewSmartyIsolate();

			// Databaseconenction
			Database::establishConnection();
		}
		catch (\Exception $ex)
		{
			Logfile::logException($ex);
            throw $ex;
		}

        // check, wether the current client is logged in
		User::checkLogin();
    }

    public static function deinit()
    {
        try {
            Database::closeConnection();
        }
        catch (\Exception $ex)
        {
            Logfile::logException($ex);
        }
    }
}
?>
