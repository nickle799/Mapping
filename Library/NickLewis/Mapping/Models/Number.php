<?php
namespace NickLewis\Mapping\Models;

class Number extends Root implements StringInterface {
	/** @type  number */
	private $value;

	/**
	 * String constructor.
	 * @param number $value
	 */
	public function __construct($value) {
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