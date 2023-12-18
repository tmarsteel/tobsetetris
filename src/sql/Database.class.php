<?php
namespace sql;

use registry\Registry;

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
	  $conn = \mysqli_connect(
			$_CONFIG["database"]["host"],
			$_CONFIG["database"]["user"],
			$_CONFIG["database"]["password"]);
		if (\mysqli_error($conn))
		{
			throw new ConnectErrorException(mysqli_error($conn), mysqli_errno($conn));
		}
                if (!\mysqli_select_db($conn, $_CONFIG["database"]["base"]))
                {
                    throw new ConnectErrorException(mysqli_error($conn), mysqli_errno($conn));
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
        return mysqli_query(self::$connection, $query);
	}
	
	/**
	 * @desc Closes the database connection
	 */
	public static function closeConnection()
	{
		if (self::$connection != null)
		{
			mysqli_close(self::$connection);
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
		return mysqli_real_escape_string(self::$connection, $str);
	}
	
	public static function getLastInsertAI()
	{
		return mysqli_insert_id(self::$connection);
	}
	
	public static function getAffectedRows()
	{
		return mysqli_affected_rows(self::$connection);
	}
        
    public static function getLastError()
    {
        return mysqli_error(self::$connection);
    }
}
?>
