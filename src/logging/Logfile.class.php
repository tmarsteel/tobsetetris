<?php
namespace logging;

use sql\MySqlException;
use sql\MySqlQueryException;

/**
 * @author Tobias Marstaller
 * @desc Writes entries to logfiles
 */
abstract class Logfile
{

    /**
     * @desc Return the date in form of [DD.MM.YYYY HH:MM:SS] of $stamp or time()
     * @return string
     * @param -int stamp The timestamp to represent
     */
    protected static function dateStr($stamp=null)
	{
        if ($stamp == null)
		{
			$stamp = time();
		}
        return "[".date("D M d Y, H:i:s", $stamp)."]";
    }

    private static function logStringToErrorLog($string) {
        global $_CONFIG;

        $logfile = $_CONFIG["logfile"]["error"];
        $logfileDir = dirname($logfile);
        if (!file_exists($logfileDir)) {
            mkdir($logfileDir, 0750, true);
        }

        $fp=fopen($logfile, "a+");
        if (!$fp)
        {
            return;
        }
        fwrite($fp, $string);
        @fclose($fp);
    }

    /**
     * @desc Logs an error to the error-logfile
     * @return void
     * @param -string message The error message
     * @param -int code An optional error Code
     */
    public static function logError($message, $code=-1)
	{
	    global $_CONFIG;

        $str=self::dateStr();
        if ($code > 0)
		{
			$str .= " (Code ".$code.")";
		}
        $str .= " ".$message."\r\n";

        self::logStringToErrorLog($str);
    }

	/**
	 * @desc Logs an MySqlException and the errous query if it is an MySqlQueryException
	 * @return void
	 * @param -MySqlException ex The exception that is to be logged.
	 */
	public static function logMySqlError(MySqlException $ex)
	{
        $str = self::dateStr()." MySQL Error(".get_class($ex)."): ".$ex->getMessage();
        if ($ex instanceof MySqlQueryException)
        {
            $cmd = str_replace("\n", "\r\n                           | ",
                $ex->getErrousCommand());
            if (!empty($cmd))
            {
                $str .= "                           | Errous Command:".
                    "\r\n                           | ".$cmd;
            }
        }
        $str .= self::formatStackTrace($ex->getTrace());

        self::logStringToErrorLog($str);
    }

	/**
	 * @desc Logs an Exception
	 * @return void
	 * @param -Exception ex The exception to be logged
	 */
	public static function logException(\Exception $ex)
	{
        global $_CONFIG;
        if ($ex instanceof MySqlException)
        {
                self::logMySqlError($ex);
                return;
        }
        $str = self::dateStr()." ".get_class($ex).": ";
        $msg = $ex->getMessage();
        if (empty($msg))
        {
                $msg = "<No Message>";
        }

        self::logStringToErrorLog($str . $msg."\r\n");
	}

	 /**
      * @desc Appends a message to the logfile
      *
      * @param string $message is the message which goes into the Log
      *
      * @return void
      */
	public static function appendLog($message)
	{
		$str='                          | '.$message."\r\n";
        self::logStringToErrorLog($str);
	}

    private static function formatStackTrace($trace)
    {
        $msg = "";
        foreach ($trace as $traceE)
        {
            $msg .= "\r\n                            at " . $traceE["file"] . ':' . $traceE["line"] . ' ';
            if (isset($traceE["function"]))
            {
                if (isset($traceE["class"]))
                {
                    $msg .= $traceE["class"] . $traceE["type"];
                }
                $msg .= $traceE["function"] . '(';
                $j = count($traceE["args"]) - 1;
                for ($i = 0;$i <= $j;$i++)
                {
                    $msg .= \gettype($traceE["args"][$i]);
                    if ($i != $j)
                    {
                        $msg .= ', ';
                    }
                }
                $msg .= ')';
            }
        }
        return $msg;
    }
}
?>