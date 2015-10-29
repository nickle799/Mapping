<?php
namespace NickLewis\Mapping\Services;

use NickLewis\Mapping\Models\ObjectInterface;

class Method {
	const RETURN_INT = 'int';
	const RETURN_BOOLEAN = 'boolean';
	const RETURN_DOUBLE = 'double';
	const RETURN_STRING = 'string';
	const RETURN_DATE = 'date';
	const RETURN_DATETIME = 'datetime';

	/** @var  string */
	private $name;
	/** @var  string */
	private $returnType;
	/** @var  callable */
	private $handler;
	/** @var  string */
	private $description;
	/** @var  bool */
	private $returnTypeMappable = false;
	/** @var Parameter[]  */
	private $parameters = [];

	/**
	 * Getter
	 * @return Parameter[]
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Setter
	 * @param Parameter[] $parameters
	 */
	private function setParameters(array $parameters) {
		$this->parameters = $parameters;
	}

	/**
	 * addParameter
	 *
	 * @param Parameter $parameter
	 *
	 * @return void
	 */
	public function addParameter(Parameter $parameter) {
		$parameters = $this->getParameters();
		$parameters[] = $parameter;
		$this->setParameters($parameters);
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Setter
	 * @param string $description
	 * @return $this
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}


	/**
	 * Getter
	 * @return callable
	 */
	private function getHandler() {
		return $this->handler;
	}

	/**
	 * handle
	 * @param array $parameters
	 * @return mixed
	 */
	public function handle(array $parameters=[]) {
		$handler = $this->getHandler();
		$parameters = $this->validateParameters($parameters);
		if(empty($parameters)) {
			return $handler();
		} else {
			return call_user_func_array($handler, $parameters);
		}
	}

	/**
	 * validParameters
	 * @param array $parameters
	 * @return array
	 * @throws CatchableException
	 */
	private function validateParameters(array $parameters=[]) {
		if(sizeOf($this->getParameters())<sizeOf($parameters)) {
			throw new CatchableException('Too many parameters passed in'."\n".$this->__toString());
		}

		foreach($this->getParameters() as $key=>$mappingParameter) {
			if(array_key_exists($key, $parameters)) {
				$parameters[$key] = $mappingParameter->validate($parameters[$key]);
			} elseif ($mappingParameter->isRequired()) {
				throw new CatchableException('Missing Required Parameter ('.$mappingParameter->getDescription().')'."\n".$this->__toString());
			}
		}
		return $parameters;
	}

	/**
	 * __toString
	 * @return string
	 */
	public function __toString() {
		$returnVar = $this->getName().': '.$this->getDescription()."\nParameters: ";
		if(empty($this->getParameters())) {
			$returnVar .= 'No Parameters';
		} else {
			foreach($this->getParameters() as $parameter) {
				$returnVar .= "\n".$parameter->__toString();
			}
		}
		return $returnVar;

	}

	/**
	 * Setter
	 * @param callable $handler
	 * @return $this
	 */
	public function setHandler(callable $handler) {
		$this->handler = $handler;
		return $this;
	}



	/**
	 * Getter
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Setter
	 * @param string $name
	 * @return $this
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getReturnType() {
		return $this->returnType;
	}

	/**
	 * getter
	 * @return boolean
	 */
	public function isReturnTypeMappable() {
		return $this->returnTypeMappable;
	}

	/**
	 * Setter
	 * @param boolean $returnTypeMappable
	 */
	private function setReturnTypeMappable($returnTypeMappable) {
		$this->returnTypeMappable = $returnTypeMappable;
	}



	/**
	 * Setter
	 * @param string $returnType
	 * @throws \Exception Invalid Return type
	 * @return $this
	 */
	public function setReturnType($returnType) {
		if(substr($returnType, -2)=='[]') {
			$compareReturnType = substr($returnType, 0, -2);
		} else {
			$compareReturnType = $returnType;
		}
		if(!in_array($compareReturnType, array(self::RETURN_BOOLEAN, self::RETURN_DOUBLE, self::RETURN_INT, self::RETURN_STRING, self::RETURN_DATE, self::RETURN_DATETIME))) {
			$class = $compareReturnType;
			if(!class_exists($class)) {
				throw new \Exception('Invalid Return Type: '.$returnType);
			}
			if(!(in_array(ObjectInterface::class, class_implements($class)))) {
				throw new \Exception('Invalid Return Type: '.$returnType.', does not implement Object Interface: '.$class);
			}
			$this->setReturnTypeMappable(true);
		}
		$this->returnType = $returnType;
		return $this;
	}

}