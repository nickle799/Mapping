<?php
namespace NickLewis\Mapping\Services;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use Bullhorn\FastRest\Api\Services\Date\Date;
use Bullhorn\FastRest\Api\Services\Date\DateTime;

class Parameter extends Root {
	/** @var bool  */
	private $required = true;
	/** @var string  */
	private $description = '';
	/** @var string */
	private $allowedType = null;

	/**
	 * Constructor
	 * @throws \Exception
	 */
	public function __construct() {
		$this->setAllowedType(Method::RETURN_STRING);
	}

	/**
	 * isRequired
	 * @return boolean
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * setRequired
	 * @param boolean $required
	 */
	public function setRequired($required) {
		$this->required = $required;
	}

	/**
	 * getDescription
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * setDescription
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * getAllowedType
	 * @return \string
	 */
	public function getAllowedType() {
		return $this->allowedType;
	}

	/**
	 * __toString
	 * @return string
	 */
	public function __toString() {
		$returnVar = '('.$this->getAllowedType().')';
		if($this->isRequired()) {
			$returnVar .= ' (Required)';
		} else {
			$returnVar .= ' (Optional)';
		}
		$returnVar .= ' '.$this->getDescription();
		return $returnVar;
	}

	/**
	 * validate
	 * @param string $value
	 * @return Date|DateTime|bool|float|int
	 */
	public function validate($value) {
		switch($this->getAllowedType()) {
			case Method::RETURN_BOOLEAN:
				$value = Assert::isBool($value);
				break;
			case Method::RETURN_DOUBLE:
				$value = Assert::isFloat($value);
				break;
			case Method::RETURN_INT:
				$value = Assert::isInt($value);
				break;
			case Method::RETURN_DATE:
				$value = new Date($value);
				break;
			case Method::RETURN_DATETIME:
				$value = new DateTime($value);
				break;
		}
		return $value;
	}

	/**
	 * Setter
	 * @param \string $allowedType
	 * @throws \Exception
	 */
	public function setAllowedType($allowedType) {
		if(!in_array($allowedType, [Method::RETURN_BOOLEAN, Method::RETURN_DOUBLE, Method::RETURN_INT, Method::RETURN_STRING, Method::RETURN_DATE, Method::RETURN_DATETIME])) {
			if(!class_exists($allowedType)) {
				throw new \Exception('Invalid Allowed Type: '.$allowedType);
			}
		}
		$this->allowedType = $allowedType;
	}


}