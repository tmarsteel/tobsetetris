<?php
namespace util\json;

/**
 * Created on 09.08.2012
 * @author Tobias Marstaller 
 */
class JSONArray implements JSONItem
{
	private $ar;
	public $length;
	private $len=0;
	public function __construct($ar = null)
    {
		if ($ar == null)
        {
			$this->ar = array();
		}
        else
        {
            $this->ar = $ar;
        }
	}
	public function add($key, $value = null)
    {
		if ($value == null)
        {
			$this->ar[] = $key;
		}
        else
        {
			$this->ar[] = $value;
		}
		$this->len++;
		$this->length = $this->len;
	}
	public function has($key)
    {
		return isset($this->ar[$key]);
	}
	public function get($key) {
		if ($this->has($key))
        {
			return $this->ar[$key];
		}
	}
	public function remove($key = null)
    {
		if ($key != null) {
			unset($this->ar[$key]);
		}
        else
        {
            unset($this->ar[count($this->ar) - 1]);
        }
		$this->len--;
		$this->length = $this->len;
	}
	public function render()
    {
		echo "[";
		$i=0;
		$j=count($this->ar);
		foreach ($this->ar as $value)
        {
			if ($value instanceof JSONItem)
            {
				$value->render();
			}
            elseif (gettype($value) == "array")
            {
				JSONObject::renderArray($value);
			}
            elseif (gettype($value)=="string")
            {
				echo '"'.$value.'"';
			}
            else
            {
                echo $value;
            }
			$i++;
			if ($i!=$j)
            {
                echo ",";
            }
		}
		echo "]";
	}
	public function renderToFile($file)
    {
		ob_start();
		$this->render();
		$x = ob_get_contents();
		ob_end_clean();
		return file_put_contents($file, $x);
	}
}
?>
