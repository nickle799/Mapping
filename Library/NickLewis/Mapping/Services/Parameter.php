<?php
namespace NickLewis\Mapping\Services;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use Bullhorn\FastRest\Api\Services\Date\Date;
use Bullhorn\FastRest\Api\Services\Date\DateTime;
use NickLewis\Mapping\Models\ObjectInterface;
use NickLewis\Mapping\Models\String;
use NickLewis\Mapping\Models\StringInterface;
use NickLewis\Mapping\Services\Lexicon\Method as LexiconMethod;

class Parameter extends Root implements ParameterInterface {
	/** @var bool  */
	private $required = true;
	/** @var string  */
	private $description = '';
	/** @var string */
	private $allowedType = null;
	/** @var bool */
	private $asParsable = false;

	/**
	 * Constructor
	 * @throws \Exception
	 */
	public function __construct() {
		$this->setAllowedType(Method::RETURN_STRING);
	}

	/**
	 * Getter
	 * @return boolean
	 */
	public function isAsParsable() {
		return $this->asParsable;
	}

	/**
	 * Setter
	 * @param boolean $asParsable
	 * @return Parameter
	 */
	public function setAsParsable($asParsable) {
		$this->asParsable = $asParsable;
		return $this;
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
	 * parseParameter
	 * @param LexiconMethod[]      $parameter
	 * @param boolean              $useOriginalObject
	 * @param ObjectInterface|null $currentObject
	 * @return ObjectInterface
	 * @throws CatchableException
	 */
	public static function parseLexiconParameter(array $parameter, $useOriginalObject=false, $currentObject=null) {
		$passedCurrentObject = $currentObject;
		$totalOutput = null;
		foreach($parameter as $subParameter) {
			if(!is_null($passedCurrentObject)) {
				$subParameter->setCurrentObject($passedCurrentObject);
			}
			$currentObject = $subParameter->call($useOriginalObject);
			if(is_null($totalOutput)) {
				$totalOutput = $currentObject;
			} elseif($currentObject instanceof StringInterface || (is_object($currentObject) && method_exists($currentObject, '__toString'))) {
				$totalOutput = new String($totalOutput . $currentObject);
			} else {
				throw new CatchableException('This object: '.get_class($currentObject).' could not be converted to a string');
			}
		}
		return $totalOutput;
	}

	/**
	 * validate
	 * @param mixed $value
	 * @return Date|DateTime|bool|float|int
	 */
	public function validate($value) {
		if($this->isAsParsable()) {
			return $value;
		} else {
			$value = self::parseLexiconParameter($value);
		}
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
		if(!in_array($allowedType, [Method::RETURN_BOOLEAN, Method::RETURN_DOUBLE, Method::RETURN_INT, Method::RETURN_STRING, Method::RETURN_DATE, Method::RETURN_DATETIME, Method::RETURN_MIXED])) {
			if(!class_exists($allowedType)) {
				throw new \Exception('Invalid Allowed Type: '.$allowedType);
			}
		}
		$this->allowedType = $allowedType;
	}


}