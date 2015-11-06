<?php
namespace NickLewis\Mapping\Services;
class ParameterGrouping extends Root implements ParameterInterface {
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
	 * __toString
	 * @return string
	 */
	public function __toString() {
		$returnVar = '0+ of the following parameter groupings:';
		foreach($this->getParameters() as $parameter) {
			$returnVar .= "\n".$parameter->__toString();
		}
		return $returnVar;
	}
}