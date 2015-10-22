<?php
namespace NickLewis\Mapping\Services;

class Sanitize {

	/**
	 * forceString
	 * @param mixed $input
	 * @return string
	 * @throws CatchableException
	 */
	public function forceString($input) {
		if(is_array($input)) {
			throw new CatchableException('Invalid Passed Type: Array - '.print_r($input, TRUE));
		}
		if(is_object($input) && !method_exists($input, '__toString')) {
			throw new CatchableException('Object that you cannot turn into string - '.print_r($input, TRUE));
		}
		return (string)$input;
	}

	/**
	 * cleanInt
	 * @param mixed $input
	 * @param bool  $forcePositive
	 * @return int
	 * @throws CatchableException
	 */
	public function cleanInt($input, $forcePositive=false) {
		if(!is_int($input)) {
			$input = $this->forceString($input);
			$input = preg_replace('@[^\d-]@', '', $input);
			$isNegative = substr($input, 0, 1)=='-';
			$input = (int)str_replace('-', '', $input); //Strip out the dashes
			if($isNegative) {
				$input *= -1;
			}
		}
		if($forcePositive) {
			$input = abs($input);
		}
		return $input;
	}

	/**
	 * Changes floats from the accounting negative format to the standard one,
	 * eg. ($3,234.34) -> -3,234.34
	 *
	 * @param string $input
	 * @return string
	 */
	private function convertParenthesisToNegative($input) {
		return preg_replace('@^\((.*)\)$@', '-\\1', trim($input));
	}

	/**
	 * cleanFloat
	 * @param mixed $input
	 * @param bool  $forcePositive
	 * @return float|int
	 * @throws CatchableException
	 */
	public function cleanFloat($input, $forcePositive=false) {
		if(!is_int($input) && !is_float($input)) {
			$input = $this->forceString($input);
			$input = $this->convertParenthesisToNegative($input);
			$input = preg_replace('@[^\d-.]@', '', $input);
			$isNegative = substr($input, 0, 1)=='-';
			$input = (float)str_replace('-', '', $input); //Strip out the dashes
			if($isNegative) {
				$input *= -1;
			}
		}
		if($forcePositive) {
			$input = abs($input);
		}
		return $input;
	}

	/**
	 * cleanBool
	 * @param $input
	 * @return bool
	 * @throws CatchableException
	 */
	public function cleanBool($input) {
		if(is_bool($input)) {
			return $input;
		}
		if(is_int($input) || is_float($input)) {
			return $input!==0;
		}
		$input = $this->forceString($input);
		$input = strtolower($input);
		return in_array($input, array('active','true','yes','1','on','y'), TRUE);
	}
}