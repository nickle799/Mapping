<?php
namespace NickLewis\Mapping\Models;


use Bullhorn\FastRest\Api\Services\Date\Date as OriginalDate;

class Date extends Root implements StringInterface {
	/** @type  OriginalDate */
	private $value;

	/**
	 * String constructor.
	 * @param OriginalDate $value
	 */
	public function __construct($value) {
		$this->setValue($value);
	}


	/**
	 * __toString
	 * @return string
	 */
	public function __toString() {
		return $this->getValue()->__toString();
	}

	/**
	 * Getter
	 * @return OriginalDate
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Setter
	 * @param OriginalDate $value
	 */
	private function setValue(OriginalDate $value) {
		$this->value = $value;
	}


}