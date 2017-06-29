<?php
namespace sql;

final class QueryCollection
{
    /**
     * @deprecated
     * @param type $id
     * @return \sql\Query
     * @throws MySqlException
     */
	public static function getQuery($id)
	{
            switch ($id)
            {
                case "selectHighscorePage":
                    return new Query("SELECT name, score FROM highscore ORDER BY score DESC LIMIT ?_0, ?_1", 2, true);

                case "insertHighscore":
                    return new Query("INSERT INTO highscore (name, score, bricksequence) VALUES (?_0, ?_1, ?_2)", 2, false);

                case "selectAllHighscore":
                    return new Query("SELECT name, score FROM highscore ORDER BY score DESC", 0, true);
            }
	}

	/**
	 * @desc Thie method directly executes the given Query and returns the number of affected rows. Use varargs for the parameters
	 * @param -string id
	 * @varargs -mixed param
	 * @throws MySqlQueryException, MySqlException
	 */
	public static function executeQuery($id)
	{
		$args = func_get_args();
		$query = self::getQuery($id);

		$j = count($args);
		for ($i = 1;$i < $j;$i++)
		{
			$query->setParam($i-1, $args[$i]);
		}
		$res = $query->execute();
		if ($res instanceof MySqlResult)
		{
			$res->free();
		}
		return $query->getAffectedRows();
	}
}
?>