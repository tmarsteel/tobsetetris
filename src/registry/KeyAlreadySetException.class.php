<?php
namespace registry;

class KeyAlreadySetException extends \Exception
{
    protected $message;
    public function __construct($key)
	{
        $this->message = "The specified key '".$key."' is already used.";
    }
}
?>