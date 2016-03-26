<?php
namespace NickLewis\Mapping\Models;

use Bullhorn\FastRest\Api\Services\DataValidation\Assert;

class String extends Root implements StringInterface {
	/** @type  string */
	private $value;

	/**
	 * String constructor.
	 * @param string $value
	 */
	public function __construct($value) {
		$value = Assert::isString($value);
		$this->setValue($value);
	}


	/**
	 * __toString
	 * @return string
	 */
	public function __toString() {
		return (string)$this->getValue();
	}

	/**
	 * Getter
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Setter
	 * @param string $value
	 */
	private function setValue($value) {
		$this->value = $value;
	}


}