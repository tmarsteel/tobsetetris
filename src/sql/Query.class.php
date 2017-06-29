<?php

namespace sql;

use logging\Logfile;
use ArgumentException;
use registry\Registry;

/**
 * @author Tobias Marstaller
 * @desc Represents a prepared SQL-Statement
 */
class Query {

    /**
     * @param -string query_str The query string that is used as a template
     * @param -bool is_select Wether this statement is a SELECT-statement
     * @param -mixed[int] args Numeric array of all parameters for the statement.
     * @param -int num_args_req The amount of parameters required for this statement.
     * @param -int num_args_giv The amount of parameters given via setParam(...)
     */
    private $queryStr;
    private $isSelect;
    private $args = array();
    private $numArgsReq = 0;
    private $numArgsGiv = 0;
    private $execAffectedRows = 0;

    /**
     * @param -string query
     * @param -bool is_select
     */
    public function __construct($query, $numArgsRequired = 0, $isSelect = false) {
        $this->queryStr = $query;
        $this->isSelect = $isSelect;
        $this->numArgsReq = $numArgsRequired;
        // Count the number of parameters for this query
        /* $match = array();
          preg_match_all("!\?\_\d+!", $query, $match);
          $args=array();
          foreach ($match[0] as $m)
          {
          if (!in_array($m, $args))
          {
          $args[]=$m;
          $this->numArgsReq++;
          }
          } */
    }

    /**
     * @param -int num
     * @param -mixed value
     */
    public function setParam($num, $value, $isField = false) {
        if (!is_int($num)) {
            return;
        }
        if (!isset($this->args[$num])) {
            $this->numArgsGiv++;
        }
        if (!is_int($value)) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            } else {
                // Convert mixed into string
                $value = "" . $value;

                // Escape all dangerous things
                $value = Database::escapeString($value);

                // If this is to embraced it is a value, e.g. `field`=?_0
                // if not, it is a field, e.g. ?_0='something'
                if ($isField) {
                    $value = '`' . $value . '`';
                } else {
                    $value = "'" . $value . "'";
                }
            }
        }
        $this->args[$num] = $value;
    }

    /**
     * @param -int $num
     * @param -mixed[] $values
     * @param -string $connectWith
     * @param -bool $isField
     * @return void
     * @desc Works ruffly the same as implode() 
     */
    public function setParams($num, array $values, $delimeter, $isField = false) {
        if (!is_int($num)) {
            return;
        }
        if (!isset($this->args[$num])) {
            $this->numArgsGiv++;
        }

        $finishedValue = '';
        $j = count($values) - 1;
        for ($i = $j; $i >= 0; $i--) {
            if (!is_int($value)) {
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } else {
                    // Convert mixed into string
                    $value = "" . $value;

                    // Escape all dangerous things
                    $value = Database::escapeString($value);

                    // If this is to embraced it is a value, e.g. `field`=?_0
                    // if not, it is a field, e.g. ?_0='something'
                    if ($isField) {
                        $value = '`' . $value . '`';
                    } else {
                        $value = "'" . $value . "'";
                    }
                }
                $finishedValue .= $value;
                if ($i != $j) {
                    $finishedValue .= $delimeter;
                }
            }
        }
        $this->args[$num] = $finishedValue;
    }

    /**
     * @desc Executes the query with the given parameters
     * @return MySqlResult
     * @throws MySqlQueryException, ArgumentException
     */
    public function execute() {
        if ($this->numArgsGiv < $this->numArgsReq) {
            throw new ArgumentException($this->numArgsReq . ' parameters required, ' .
            $this->numArgsGiv . ' given.');
        }

        $database = Registry::get('db_conn');

        $query = $this->getFinishedQueryString();

        $result = Database::execute($query);
        if ($result === FALSE) {
            $ex = new MySqlQueryException(Database::getLastError(), $query);
            Logfile::logMySqlError($ex);
            throw $ex;
        }
        
        $this->execAffectedRows = Database::getAffectedRows();

        if ($this->isSelect) {
            return new MySqlResult($result);
        }
    }

    /**
     * @desc Deletes the list of all parameters
     * @return void
     */
    public function reset() {
        $this->args = array();
        $this->numArgsGiv = 0;
    }

    public function getAffectedRows() {
        return $this->execAffectedRows;
    }

    public static function get($id) {
        return QueryCollection::getQuery($id);
    }

    public function getRawQueryString() {
        return $this->queryStr;
    }

    public function getFinishedQueryString() {
        if ($this->numArgsGiv < $this->numArgsReq) {
            throw new ArgumentException($this->numArgsReq . ' parameters required, ' .
            $this->numArgsGiv . ' given.');
        }
        $query = $this->queryStr; // Copy of the original
        $j = count($this->args) - 1;
        for ($num = $j; $num >= 0; $num--) {
            $query = str_replace('?_' . $num, $this->args[$num], $query);
        }
        return $query;
    }

}

?>