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
			$this->addAdd()
		];
	}

	/**
	 * addDate
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
	 * @return NumberInterface
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @param NumberInterface $model
	 * @return String
	 */
	public function setModel($model) {
		$this->model = $model;
		return $this;
	}


}