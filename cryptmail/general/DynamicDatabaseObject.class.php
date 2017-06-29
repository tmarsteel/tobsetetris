<?php
namespace cryptmail\general;

use \cryptmail\sql\QueryCollection;
use \cryptmail\sql\Query;

abstract class DynamicDatabaseObject
{
	/**
	 * @param -mixed[string] properties
	 * @param -Query fieldQuery
	 * @param -string propNull
	 */

	protected $properties = array();
	protected $fieldQuery = null;
	protected $propNull;

	/**
	 * @desc Reads the given property from the database. $propertyName must equal the database column name
	 * @return mixed
	 * @param -string propertyName
	 */
	protected function getProperty($propertyName)
	{
		if (isset($this->properties[$propertyName]))
		{
			return $this->properties[$propertyName];
		}
		$this->fieldQuery->reset();
		$this->fieldQuery->setParam(0, $this->propNull);
		$this->fieldQuery->setParam(1, $propertyName, true);
		try
		{
			$res = $this->fieldQuery->execute();
			if ($res->getNumRows() == 0)
			{
				throw new NotFoundException();
			}
			$row = $res->asArray();
			$res->free();
			$this->properties[$propertyName] = $row[$propertyName];
			return $row[$propertyName];
		}
		catch (MySqlException $ex)
		{
			Logfile::logMySqlError($ex);
			throw new NotFoundException();
		}
	}

	/**
	 * @desc Reads the given properties from the database. all $porpertyName's must equals the databse column names
	 * @return mixed[]
	 * @vararg -string propertyName
	 * @param -string propertyName
	 * @throws MySqlException
	 */
	protected function getMultipleProperties()
	{
		$props = func_get_args();

		$returnArray = array();

		foreach ($props as $key => $param)
		{
			if (isset($this->properties[$param]))
			{
				unset($props[$key]);
				$returnArray[$param] = $this->properties[$param];
			}
		}
		$props = array_merge(array(), $props);

		$this->fieldQuery->reset();
		$this->fieldQuery->setParam(0, $this->propNull);
		$this->fieldQuery->setParams(1, $props, ',', true);
		$res = $this->fieldQuery->execute();
		if ($res->getNumRows() != 0)
		{
			$data = $res->asAssocArray();
			foreach ($data as $key => $value)
			{
				$returnArray[$key] = $value;
			}
		}
		$res->free();

		return $returnArray;
	}
}
?>