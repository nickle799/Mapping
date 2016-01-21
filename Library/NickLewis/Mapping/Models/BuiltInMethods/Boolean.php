<?php
namespace NickLewis\Mapping\Models\BuiltInMethods;
use NickLewis\Mapping\Models\BooleanInterface;
use NickLewis\Mapping\Models\ObjectInterface;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;

class Boolean {
	/** @type  BooleanInterface */
	private $model;

	/**
	 * String constructor.
	 * @param BooleanInterface $model
	 */
	public function __construct(BooleanInterface $model) {
		$this->setModel($model);
	}

	/**
	 * addMethods
	 * @return Method[]
	 */
	public function addMethods() {
		return [
			$this->addIfThen(),
			$this->addNot()
		];
	}

	/**
	 * addNot
	 * @return Method
	 * @throws \Exception
	 */
	private function addNot() {
		$method = new Method();
		$method->setName('not');
		$method->setDescription('Converts true to false and false to true');
		$method->setReturnType(Method::RETURN_BOOLEAN);

		$method->setHandler([$this, 'mappableNot']);
		return $method;
	}

	/**
	 * mappableNot
	 * @return bool
	 */
	public function mappableNot() {
		if($this->getModel()->getValue()) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * addIfThen
	 * @return Method
	 * @throws \Exception
	 */
	private function addIfThen() {
		$method = new Method();
		$method->setName('ifThen');
		$method->setDescription('If true, uses the first parameter, otherwise uses the second parameter');
		$method->setReturnType(Method::RETURN_MIXED);

		$parameter = new Parameter();
		$parameter->setAllowedType(Method::RETURN_MIXED);
		$parameter->setDescription('The true parameter');
		$method->addParameter($parameter);

		$parameter = new Parameter();
		$parameter->setRequired(false);
		$parameter->setAllowedType(Method::RETURN_MIXED);
		$parameter->setDescription('The false parameter');
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableIfThen']);
		return $method;
	}

	/**
	 * mappableIfThen
	 * @param ObjectInterface $true
	 * @param ObjectInterface $false
	 * @return ObjectInterface
	 */
	public function mappableIfThen($true, ObjectInterface $false=null) {
		if($this->getModel()->getValue()) {
			return $true;
		} else {
			return $false;
		}
	}

	/**
	 * @return BooleanInterface
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @param BooleanInterface $model
	 * @return String
	 */
	public function setModel(BooleanInterface $model) {
		$this->model = $model;
		return $this;
	}


}