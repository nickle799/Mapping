<?php
namespace NickLewis\Mapping\Models;
interface NumberInterface extends StringInterface {
	/**
	 * Getter
	 * @return number
	 */
	public function getValue();
}