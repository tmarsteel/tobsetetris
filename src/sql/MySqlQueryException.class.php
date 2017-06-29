<?php
namespace sql;

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