<?php
namespace NickLewis\Mapping\Models\BuiltInMethods;
use NickLewis\Mapping\Models\NumberInterface;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;

class Number {
	/** @type  NumberInterface */
	private $model;

	/**
	 * String constructor.
	 * @param NumberInterface $model
	 */
	public function __construct(NumberInterface $model) {
		$this->setModel($model);
	}

	/**
	 * addMethods
	 * @return Method[]
	 */
	public function addMethods() {
		return [
			$this->addAdd(),
			$this->addSubtract(),
			$this->addMultiply(),
			$this->addDivide(),
			$this->addRound(),
			$this->addGreaterThan(),
			$this->addLessThan()
		];
	}

	/**
	 * addAdd
	 * @return Method
	 */
	private function addAdd() {
		$method = new Method();
		$method->setName('add');
		$method->setDescription('Adds two numbers together');
		$method->setReturnType(Method::RETURN_DOUBLE);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_DOUBLE);
		$parameter->setDescription('The parameter to add to');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableAdd']);
		return $method;
	}

	/**
	 * mappableAdd
	 * @param number $number
	 * @return number
	 */
	public function mappableAdd($number) {
		return $this->getModel()->getValue()+$number;
	}

	/**
	 * addSubtract
	 * @return Method
	 */
	private function addSubtract() {
		$method = new Method();
		$method->setName('subtract');
		$method->setDescription('Subtracts two numbers');
		$method->setReturnType(Method::RETURN_DOUBLE);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_DOUBLE);
		$parameter->setDescription('The parameter to subtract');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableSubtract']);
		return $method;
	}

	/**
	 * mappableSubtract
	 * @param number $number
	 * @return number
	 */
	public function mappableSubtract($number) {
		return $this->getModel()->getValue()-$number;
	}

	/**
	 * addGreaterThan
	 * @return Method
	 */
	private function addGreaterThan() {
		$method = new Method();
		$method->setName('greaterThan');
		$method->setDescription('Checks if the current value is greater than the passed in value');
		$method->setReturnType(Method::RETURN_BOOLEAN);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_DOUBLE);
		$parameter->setDescription('The parameter to check against');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableGreaterThan']);
		return $method;
	}

	/**
	 * mappableGreaterThan
	 * @param number $number
	 * @return number
	 */
	public function mappableGreaterThan($number) {
		return $this->getModel()->getValue()>$number;
	}

	/**
	 * addLessThan
	 * @return Method
	 */
	private function addLessThan() {
		$method = new Method();
		$method->setName('lessThan');
		$method->setDescription('Checks if the current value is less than the passed in value');
		$method->setReturnType(Method::RETURN_BOOLEAN);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_DOUBLE);
		$parameter->setDescription('The parameter to check against');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableLessThan']);
		return $method;
	}

	/**
	 * mappableLessThan
	 * @param number $number
	 * @return number
	 */
	public function mappableLessThan($number) {
		return $this->getModel()->getValue()<$number;
	}

	/**
	 * addMultiply
	 * @return Method
	 */
	private function addMultiply() {
		$method = new Method();
		$method->setName('multiply');
		$method->setDescription('Multiplies two numbers');
		$method->setReturnType(Method::RETURN_DOUBLE);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_DOUBLE);
		$parameter->setDescription('The parameter to multiply');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableMultiply']);
		return $method;
	}

	/**
	 * mappableMultiply
	 * @param number $number
	 * @return number
	 */
	public function mappableMultiply($number) {
		return $this->getModel()->getValue()*$number;
	}

	/**
	 * addDivide
	 * @return Method
	 */
	private function addDivide() {
		$method = new Method();
		$method->setName('divide');
		$method->setDescription('Divides two numbers');
		$method->setReturnType(Method::RETURN_DOUBLE);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_DOUBLE);
		$parameter->setDescription('The parameter to divide');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableDivide']);
		return $method;
	}

	/**
	 * mappableDivide
	 * @param number $number
	 * @return number
	 */
	public function mappableDivide($number) {
		return round($this->getModel()->getValue()/$number, 6);
	}

	/**
	 * addRound
	 * @return Method
	 */
	private function addRound() {
		$method = new Method();
		$method->setName('round');
		$method->setDescription('Rounds two numbers');
		$method->setReturnType(Method::RETURN_DOUBLE);

		$parameter = new Parameter();
		$parameter->setRequired(false);
		$parameter->setAllowedType(Method::RETURN_DOUBLE);
		$parameter->setDescription('The Precision to round by');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableRound']);
		return $method;
	}

	/**
	 * mappableRound
	 * @param int $precision
	 * @return float
	 */
	public function mappableRound($precision=0) {
		return round($this->getModel()->getValue(), $precision);
	}

	/**
	 * @return NumberInterface
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @param NumberInterface $model
	 * @return String
	 */
	public function setModel(NumberInterface $model) {
		$this->model = $model;
		return $this;
	}


}