<?php
namespace cryptmail\sql;

class ConnectErrorException extends MySqlException
{
	
	public function __construct($message, $errno)
	{
		$this->message = "(Code ".$errno.") ".$message;
	}
}
?>