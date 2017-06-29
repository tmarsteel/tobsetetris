<?php
namespace cryptmail\core\registry;

class KeyNotSetException extends \Exception
{
    protected $message;
    public function __construct($key)
	{
        $this->message = "The specified key '".$key."' was not set.";
    }
}
?>