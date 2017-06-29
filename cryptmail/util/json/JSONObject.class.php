<?php
namespace cryptmail\util\json;

/**
 * Created on 09.08.2012
 * @author Tobias Marstaller 
 */
class JSONObject implements JSONItem
{
	private $obj;
    
	public function __construct()
    {
		$this->obj = array();
	}
	public function add($key, $value)
    {
		$this->obj[$key] = $value;
	}
	public function has($key)
    {
		return isset($this->obj[$key]);
	}
	public function get($key)
    {
		if ($this->has($key)) {
			return $this->obj[$key];
		}
	}
	public function remove($key = null)
    {
		if ($key != null)
        {
			unset($this->obj[$key]);
		}
        else
        {
            unset($this->obj[count($this->obj) - 1]);
        }
	}
	public function render()
        {
            echo "{";
            $i = 0;
            $j = count($this->obj);
            foreach ($this->obj as $key => $object)
            {
                $this->renderKey($key);
                if (gettype($object) == "array")
                {
                    $this->renderArray($object);
                }
                else if (gettype($object) == "string")
                {
                    echo '"' . $object . '"';
                }
                else if ($object instanceof JSONItem)
                {
                    $object->render();
                }
                else
                {
                    echo $object;
                }
                $i++;
                if ($i != $j)
                {
                    echo ",";
                }
            }
            echo "}";
        }
	public function renderToFile($file)
        {
		ob_start();
		$this->render();
		$x = ob_get_contents();
		ob_end_clean();
		return file_put_contents($file, $x);
	}
        public function __toString()
        {
            ob_start();
            $this->render();
            $x = ob_get_contents();
            ob_end_clean();
            return $x;
        }
	
	public static function renderKey($key)
        {	
            if (gettype($key) == "string")
            {
                echo '"' . $key . '"';
            }
            else
            {
                echo $key;
            }
            echo ":";
        }
	public static function renderArray($ar)
        {
            $ar = new JSONArray($ar);
            $ar->render();
	}
}

?>
