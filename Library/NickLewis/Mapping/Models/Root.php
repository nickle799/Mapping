<?php
namespace NickLewis\Mapping\Models;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;

abstract class Root implements ObjectInterface {
	/**
	 * getStringMethods
	 * @return Method[]
	 */
	private function getStringMethods() {
		$returnVar = [];
		if(!($this instanceof StringInterface)) {
			return $returnVar;
		}
		/** @type Root $this */
		$method = new Method();
		$method->setName('date');
		$method->setReturnType(Method::RETURN_STRING);
		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_STRING);
		$parameter->setDescription('The format for the date (see http://php.net/manual/en/function.date.php)');
		$method->addParameter($parameter);
		$method->setHandler([$this, 'mappableDate']);
		$returnVar[] = $method;
		return $returnVar;
	}

	/**
	 * mappableDate
	 * @param $format
	 * @return string
	 */
	public function mappableDate($format) {
		/** @type StringInterface $this */
		return date($format, strtotime($this->__toString()));
	}

	/**
	 * This gets a list of all available mappable fields
	 * The Key should be the name of the field, and the value should be what it returns
	 * If it returns something other than double|int|string|date|datetime, it will assume it is an ObjectInterface and look up the children
	 * @return Method[]
	 */
	public function getMappableFields() {
		$returnVar = [];
		$returnVar = array_merge($returnVar, $this->getStringMethods());
		return $returnVar;
	}

}