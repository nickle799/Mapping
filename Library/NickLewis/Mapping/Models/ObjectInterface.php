<?php
namespace NickLewis\Mapping\Models;
use NickLewis\Mapping\Services\Method;
interface ObjectInterface {
	/**
	 * This gets a list of all available mappable fields
	 * The Key should be the name of the field, and the value should be what it returns
	 * If it returns something other than double|int|string|date|datetime, it will assume it is an ObjectInterface and look up the children
	 * @return Method[]
	 */
	public function getMappableFields();
}