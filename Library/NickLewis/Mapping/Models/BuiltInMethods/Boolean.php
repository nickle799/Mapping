<?php
namespace NickLewis\Mapping\Models\BuiltInMethods;
use NickLewis\Mapping\Models\BooleanInterface;
use NickLewis\Mapping\Models\ObjectInterface;
use NickLewis\Mapping\Models\StringInterface;
use NickLewis\Mapping\Services\CatchableException;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;
use NickLewis\Mapping\Services\Lexicon\Method as LexiconMethod;

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
		$parameter->setAsParsable(true);
		$method->addParameter($parameter);

		$parameter = new Parameter();
		$parameter->setRequired(false);
		$parameter->setAllowedType(Method::RETURN_MIXED);
		$parameter->setDescription('The false parameter');
		$parameter->setAsParsable(true);
		$method->addParameter($parameter);

		$method->setHandler([$this, 'mappableIfThen']);
		return $method;
	}

	/**
	 * mappableIfThen
	 * @param LexiconMethod[] $true
	 * @param LexiconMethod[] $false
	 * @return ObjectInterface
	 * @throws CatchableException
	 */
	public function mappableIfThen($true, $false=null) {
		if($this->getModel()->getValue()) {
			$objectInterface = Parameter::parseLexiconParameter($true);
		} else {
			$objectInterface = Parameter::parseLexiconParameter($false);
		}
		return $objectInterface;
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