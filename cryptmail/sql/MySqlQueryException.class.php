<?php
namespace cryptmail\sql;
include_once(dirname(__FILE__)."/MySqlException.class.php");

class MySqlQueryException extends MySqlException {

    private $query;
    
    public function __construct($msg, $query=null) {
        $this->message=$msg;
        $this->query=$query;
    }
    
    public function getErrousCommand() {
        return $this->query;
    }
}
?>