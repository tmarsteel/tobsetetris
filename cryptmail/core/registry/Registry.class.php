<?php
namespace cryptmail\core\registry;

/**
 * @desc A globally available class to seperate information from the $_GLOBALS array
 */
abstract class Registry
{
    private static $values=array();

    /**
     * @param -String key The to be stored associated with its value. If already used, a KeyAlreadySetException will be thrown.
     * @param -mixed value Anything to be stored along the key
     * @return void
     */
    public static function set($key, $value)
	{
        if ($key === null || $value === null) return;
        if (!self::has($key))
		{
            self::$values[$key] = $value;
        }
		else
		{
            throw new KeyAlreadySetException($key);
        }
    }

    /**
     * @param -String key The key wichs association is to be returned. If the key is not found, a KeyNotSetException is thrown
     * @return mixed
     */
    public static function get($key)
	{
        if (self::has($key))
		{
            return self::$values[$key];
        }
		else
		{
            throw new KeyNotSetException($key);
        }
    }

    /**
     * @desc Removes the specified key-value pair. Returns the object that was removed.
     * @param -String key The key of the key-value pair to be removed. If the key is not found, a KeyNotSetException is thrown
     * @return mixed
     */
    public static function pop($key)
	{
        if (self::has($key))
		{
            $obj = self::$values[$key];
            self::$values[$key] = null;
            return $obj;
        }
		else
		{
            throw new KeyNotSetException($key);
        }
    }

    /**
     * @desc Returns weather the specified key has already been set
     * @param -String key The key to be checked for. No exception will be thrown.
     * @return boolean
     */
    public static function has($key)
	{
        return isset(self::$values[$key]);
    }
}
?>