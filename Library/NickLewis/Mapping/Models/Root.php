<?php
namespace NickLewis\Mapping\Models;
use NickLewis\Mapping\Models\BuiltInMethods\Boolean as BuiltInBoolean;
use NickLewis\Mapping\Models\BuiltInMethods\String as BuiltInString;
use NickLewis\Mapping\Models\BuiltInMethods\Number as BuiltInNumber;
use NickLewis\Mapping\Models\BuiltInMethods\Map as BuiltInMap;
use NickLewis\Mapping\Services\Method;

abstract class Root implements ObjectInterface {
	/**
	 * getStringMethods
	 * @return Method[]
	 */
	private function getStringMethods() {
		if(!($this instanceof StringInterface)) {
			return [];
		}
		$string = new BuiltInString($this);
		return $string->addMethods();
	}

	/**
	 * getNumberMethods
	 * @return Method[]
	 */
	private function getNumberMethods() {
		if(!($this instanceof NumberInterface)) {
			return [];
		}
		$string = new BuiltInNumber($this);
		return $string->addMethods();
	}

	/**
	 * getNumberMethods
	 * @return Method[]
	 */
	private function getBooleanMethods() {
		if(!($this instanceof BooleanInterface)) {
			return [];
		}
		$string = new BuiltInBoolean($this);
		return $string->addMethods();
	}

	private function getMapMethods() {
		if(!($this instanceof MapInterface)) {
			return [];
		}
		$string = new BuiltInMap($this);
		return $string->addMethods();
	}

	/**
	 * This gets a list of all available mappable fields
	 * The Key should be the name of the field, and the value should be what it returns
	 * If it returns something other than double|int|string|date|datetime, it will assume it is an ObjectInterface and look up the children
	 * @return Method[]
	 */
	public function getMappableFields() {
		$returnVar = array_merge(
			$this->getStringMethods(),
			$this->getNumberMethods(),
			$this->getBooleanMethods(),
			$this->getMapMethods()
		);
		return $returnVar;
	}

}