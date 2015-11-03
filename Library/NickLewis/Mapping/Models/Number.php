<?php
namespace NickLewis\Mapping\Models;

use Bullhorn\FastRest\Api\Services\DataValidation\Assert;

class Number extends Root implements NumberInterface {
	/** @type  number */
	private $value;

	/**
	 * String constructor.
	 * @param number $value
	 */
	public function __construct($value) {
		$value = Assert::isFloat($value);
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
	 * @return number
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Setter
	 * @param number $value
	 */
	private function setValue($value) {
		$this->value = $value;
	}


}