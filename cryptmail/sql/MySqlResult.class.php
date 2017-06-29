<?php
namespace cryptmail\sql;

/**
 * @author Tobias Marstalelr
 * @desc Represents the result of a MySQL SELECT-request
 */
class MySqlResult
{
    /**
     * @param -resource The result handle
     * @param -resource The connection handle
     */
    private $handle;
    
    /**
     * @access private
     * @param -resource The result handle
     * @param -resource The connection handle
     */
    function __construct($result)
	{
        $this->handle=$result;
    }
    
    /**
     * @desc Returns the number of rows conatined in the result
     * @return int
     */
    public function getNumRows()
	{
        return $this->handle->num_rows;
    }
    
    /**
     * @desc Returns the next row or FALSE if the end has been reached
     * @return mixed[]
     */
    public function asArray() {
        //return $this->handle->fetch_array();
        return mysql_fetch_array($this->handle);
    }
    
    /**
     * @desc Returns the next row or FALSE if the end has been reached
     * @return mixed[]
     */
    public function asAssocArray() {
        //return $this->handle->fetch_assoc();
        return mysql_fetch_assoc($this->handle);
    }
    
    /**
     * @desc Returns the next row or FALSE if the end has been reached
     * @return mixed[]
     */
    public function asNumericArray() {
        //return $this->handle->fetch_array(MYSQL_NUM);
        return mysql_fetch_array($this->handle, MYSQL_NUM);
    }
    
    /**
     * @desc Returns the next row or FALSE if the end has been reached
     * @return object
     */
    public function asObject() {
        //return $this->handle->fetch_object();
        return mysql_fetch_object($this->handle);
    }
    
    /**
     * @desc Frees the memory of this result
     * @return void
     */ 
    public function free() {
        //$this->handle->free();
        mysql_free_result($this->handle);
    }
    
	/**
     * @desc Frees the memory of this result
     * @return void
     */ 
    public function close() {
        $this->free();
    }
}
?>