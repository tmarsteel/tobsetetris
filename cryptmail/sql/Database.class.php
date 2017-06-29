<?php
namespace cryptmail\sql;

use \cryptmail\core\registry\Registry;
use \mysqli;

/**
 * @author Tobias Marstaller
 * @desc Represents the connection to the Database
 */
abstract class Database
{
	
	private static $connection;

	/**
	 * @desc Establishes the database connection using the config values
	 * @throws ConnectErrorException
	 */
	public static function establishConnection()
	{
		global $_CONFIG;
		@$conn = mysql_connect(
			$_CONFIG["database"]["host"],
			$_CONFIG["database"]["user"],
			$_CONFIG["database"]["password"]);
		if (mysql_error($conn))
		{
			throw new ConnectErrorException(mysql_error($conn), mysql_errno($conn));
		}
                if (!mysql_select_db($_CONFIG["database"]["base"], $conn))
                {
                    throw new ConnectErrorException(mysql_error($conn), mysql_errno($conn));
                }
		self::$connection = $conn;
		Registry::set("db_conn", $conn);
	}
	
	/**
	 * @desc Executes the query if the connection is stable
	 * @param -string query The sql-statement to be executed
	 * @return mixed
	 * @throws MySqlQueryException, MySqlException
	 */
	public static function execute($query)
	{
            if (self::$connection == null)
            {
                    throw new MySqlException("No connection established");
            }
            return mysql_query($query, self::$connection);
	}
	
	/**
	 * @desc Closes the database connection
	 */
	public static function closeConnection()
	{
		if (self::$connection != null)
		{
			mysql_close(self::$connection);
		}
	}
	
	/**
	 * @desc Changes the given string in such a way, it will not allow any sql-injections
	 * @return string
	 * @param -strnig str
	 */
	public static function escapeString($str)
	{
		if (self::$connection == null)
		{
			return addslashes($str);
		}
		return mysql_real_escape_string($str, self::$connection);
	}
	
	public static function getLastInsertAI()
	{
		return mysql_insert_id(self::$connection);
	}
	
	public static function getAffectedRows()
	{
		return mysql_affected_rows(self::$connection);
	}
        
        public static function getLastError()
        {
            return mysql_error(self::$connection);
        }
}
?>