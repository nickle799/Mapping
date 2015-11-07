<?php
namespace NickLewis\Mapping\Models;

use Bullhorn\FastRest\Api\Services\DataValidation\Assert;

class Boolean extends Root implements BooleanInterface {
	/** @type  bool */
	private $value;

	/**
	 * String constructor.
	 * @param bool $value
	 */
	public function __construct($value) {
		$value = Assert::isBool($value);
		$this->setValue($value);
	}


	/**
	 * __toString
	 * @return string
	 */
	public function __toString() {
		return $this->getValue()?'yes':'no';
	}

	/**
	 * Getter
	 * @return bool
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Setter
	 * @param bool $value
	 */
	private function setValue($value) {
		$this->value = $value;
	}


}