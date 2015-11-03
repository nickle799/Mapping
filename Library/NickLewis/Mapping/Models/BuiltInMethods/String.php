<?php
namespace NickLewis\Mapping\Models\BuiltInMethods;
use NickLewis\Mapping\Models\StringInterface;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;

class String {
	/** @type  StringInterface */
	private $model;

	/**
	 * String constructor.
	 * @param StringInterface $model
	 */
	public function __construct(StringInterface $model) {
		$this->setModel($model);
	}

	/**
	 * addMethods
	 * @return Method[]
	 */
	public function addMethods() {
		return [
			$this->addDate(),
			$this->addSubString()
		];
	}

	private function addSubString() {
		$method = new Method();
		$method->setName('substring');
		$method->setDescription('See http://php.net/manual/en/function.substr.php');
		$method->setReturnType(Method::RETURN_STRING);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_INT);
		$parameter->setDescription('The offset from the beginning of the string.  If it is negative, it will be offset from the end of the string');
		$method->addParameter($parameter);

		$parameter = new Parameter();
		$parameter->setRequired(false);
		$parameter->setAllowedType(Method::RETURN_INT);
		$parameter->setDescription('The length of the substring.  If it is negative, it starts at the end of the string.  If this parameter is not passed, then it will return the end of the string');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableSubString']);
		return $method;
	}

	/**
	 * mappableSubString
	 * @param int $start
	 * @param int $length
	 * @return string
	 */
	public function mappableSubString($start, $length=null) {
		if(is_null($length)) {
			return substr($this->getModel()->__toString(), $start);
		} else {
			return substr($this->getModel()->__toString(), $start, $length);
		}
	}

	/**
	 * addDate
	 * @return Method
	 */
	private function addDate() {
		$method = new Method();
		$method->setName('date');
		$method->setDescription('Formats a string into a date format');
		$method->setReturnType(Method::RETURN_STRING);
		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_STRING);
		$parameter->setDescription('The format for the date (see http://php.net/manual/en/function.date.php)');
		$method->addParameter($parameter);
		$method->setHandler([$this, 'mappableDate']);
		return $method;
	}

	/**
	 * mappableDate
	 * @param string $format
	 * @return string
	 */
	public function mappableDate($format) {
		return date($format, strtotime($this->getModel()->__toString()));
	}

	/**
	 * @return StringInterface
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @param StringInterface $model
	 * @return String
	 */
	public function setModel($model) {
		$this->model = $model;
		return $this;
	}


}