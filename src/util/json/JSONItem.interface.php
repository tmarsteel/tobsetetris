<?php
namespace util\json;

/**
 * Created on 09.08.2012
 * @author Tobias Marstaller 
 */

interface JSONItem
{
	public function add($key, $value);
	public function remove($key=null);
	public function render();
	public function renderToFile($file);
}
?>
