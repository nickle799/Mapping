<?php
namespace NickLewis\Mapping\Models\BuiltInMethods;
use NickLewis\Mapping\Models\StringInterface;
use NickLewis\Mapping\Services\CatchableException;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;
use NickLewis\Mapping\Services\ParameterGrouping;

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
			$this->addSubString(),
			$this->addIn(),
			$this->addToLowerCase(),
			$this->addToUpperCase(),
			$this->addTrim(),
			$this->addLeftFill(),
			$this->addRightFill()
		];
	}

	/**
	 * addIfThen
	 * @return Method
	 * @throws \Exception
	 */
	private function addIn() {
		$method = new Method();
		$method->setName('in');
		$method->setDescription('Returns true if the current value is in any of the parameters');
		$method->setReturnType(Method::RETURN_BOOLEAN);

		$parameterGrouping = new ParameterGrouping();
		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_STRING);
		$parameter->setDescription('A string to check against');
		$parameterGrouping->addParameter($parameter);

		$method->addParameter($parameterGrouping);

		$method->setHandler([$this, 'mappableIn']);
		return $method;
	}

	/**
	 * mappableIn
	 * @return bool
	 */
	public function mappableIn() {
		$arguments = func_get_args();
		return in_array($this->getModel()->__toString(), $arguments);
	}

	/**
	 * addSubString
	 * @return Method
	 * @throws \Exception
	 */
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
	 * addToLowerCase
	 * @return Method
	 * @throws \Exception
	 */
	private function addToLowerCase() {
		$method = new Method();
		$method->setName('toLowerCase');
		$method->setDescription('Makes the string all lowercase');
		$method->setReturnType(Method::RETURN_STRING);

		$method->setHandler([$this, 'mappableToLowerCase']);
		return $method;
	}
	
	/**
	 * mappableToLowerCase
	 * @return string
	 */
	public function mappableToLowerCase() {
		return strtolower($this->getModel()->__toString());
	}

	/**
	 * addToUpperCase
	 * @return Method
	 * @throws \Exception
	 */
	private function addToUpperCase() {
		$method = new Method();
		$method->setName('toUpperCase');
		$method->setDescription('Makes the string all uppercase');
		$method->setReturnType(Method::RETURN_STRING);

		$method->setHandler([$this, 'mappableToUpperCase']);
		return $method;
	}
	
	/**
	 * mappableToUpperCase
	 * @return string
	 */
	public function mappableToUpperCase() {
		return strtoupper($this->getModel()->__toString());
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
	 * addTrim
	 * @return Method
	 */
	private function addTrim() {
		$method = new Method();
		$method->setName('trim');
		$method->setDescription('Trims empty spaces from either end of a string');
		$method->setReturnType(Method::RETURN_STRING);
		$parameter = new Parameter();
		$parameter->setRequired(false);
		$parameter->setAllowedType(Method::RETURN_STRING);
		$parameter->setDescription('The specific characters to trim.  It is whitespace by default');
		$method->addParameter($parameter);
		$method->setHandler([$this, 'mappableTrim']);
		return $method;
	}

	/**
	 * mappableTrim
	 * @param string $characters
	 * @return string
	 */
	public function mappableTrim($characters=null) {
		if(is_null($characters)) {
			return trim($this->getModel()->__toString());
		} else {
			return trim($this->getModel()->__toString(), $characters);
		}
	}

	/**
	 * addTrim
	 * @return Method
	 */
	private function addLeftFill() {
		$method = new Method();
		$method->setName('leftFill');
		$method->setDescription('This adds characters to the left side of the string');
		$method->setReturnType(Method::RETURN_STRING);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_INT);
		$parameter->setDescription('The length to fill the string to');
		$method->addParameter($parameter);

		$parameter = new Parameter();
		$parameter->setRequired(false);
		$parameter->setAllowedType(Method::RETURN_STRING);
		$parameter->setDescription('The string to fill it with.  Defaults to 0');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableLeftFill']);
		return $method;
	}
	
	/**
	 * mappableLeftFill
	 * @param int    $length
	 * @param string $fillString
	 * @return string
	 * @throws CatchableException
	 */
	public function mappableLeftFill($length, $fillString='0') {
		if(strlen($fillString)=='') {
			throw new CatchableException('Fill String Cannot be empty');
		}
		return str_pad($this->getModel()->__toString(), $length, $fillString, STR_PAD_LEFT);
	}

	/**
	 * addTrim
	 * @return Method
	 */
	private function addRightFill() {
		$method = new Method();
		$method->setName('rightFill');
		$method->setDescription('This adds characters to the right side of the string');
		$method->setReturnType(Method::RETURN_STRING);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_INT);
		$parameter->setDescription('The length to fill the string to');
		$method->addParameter($parameter);

		$parameter = new Parameter();
		$parameter->setRequired(false);
		$parameter->setAllowedType(Method::RETURN_STRING);
		$parameter->setDescription('The string to fill it with.  Defaults to " "');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableRightFill']);
		return $method;
	}
	
	/**
	 * mappableRightFill
	 * @param int    $length
	 * @param string $fillString
	 * @return string
	 * @throws CatchableException
	 */
	public function mappableRightFill($length, $fillString=' ') {
		if(strlen($fillString)=='') {
			throw new CatchableException('Fill String Cannot be empty');
		}
		return str_pad($this->getModel()->__toString(), $length, $fillString, STR_PAD_RIGHT);
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
	public function setModel(StringInterface $model) {
		$this->model = $model;
		return $this;
	}


}