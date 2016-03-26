<?php
namespace NickLewis\Mapping\Services;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use NickLewis\Mapping\Models\Boolean;
use NickLewis\Mapping\Models\Date;
use NickLewis\Mapping\Models\Map;
use NickLewis\Mapping\Models\Number;
use NickLewis\Mapping\Models\ObjectInterface;
use NickLewis\Mapping\Models\Parsable;
use NickLewis\Mapping\Models\String;
use NickLewis\Mapping\Models\StringInterface;
use NickLewis\Mapping\Services\Lexicon\Parser;

class Parse {
	/** @var  ObjectInterface */
	private $originalObject;
	/** @type  string */
	private $originalMapping;
	/** @type  mixed */
	private $totalOutput;
	const UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS = 2;
	const UNTIL_END = 1;
	/** @type int  */
	private $lastIndex = 0;

	/**
	 * @param ObjectInterface $currentObject
	 */
	public function __construct(ObjectInterface $currentObject) {
		$this->setOriginalObject($currentObject);
	}

	/**
	 * createParse
	 * @param mixed $currentObject
	 * @return Parse
	 */
	public static function createParse($currentObject) {
		if($currentObject instanceof ObjectInterface) {
			$object = $currentObject;
		} elseif(is_numeric($currentObject)) {
			$object = new Number($currentObject);
		} elseif(is_array($currentObject)) {
			$object = new Map($currentObject);
		} elseif(is_bool($currentObject)) {
			$object = new Boolean($currentObject);
		} else {
			$object = new String($currentObject);
		}
		return new self($object);
	}

	/**
	 * Getter
	 * @return int
	 */
	private function getLastIndex() {
		return $this->lastIndex;
	}

	/**
	 * Setter
	 * @param int $lastIndex
	 */
	private function setLastIndex($lastIndex) {
		$this->lastIndex = $lastIndex;
	}



	/**
	 * Getter
	 * @return mixed
	 */
	private function getTotalOutput() {
		return $this->totalOutput;
	}

	/**
	 * Setter
	 * @param mixed $totalOutput
	 */
	private function setTotalOutput($totalOutput) {
		$this->totalOutput = $totalOutput;
	}

	/**
	 * Getter
	 * @return ObjectInterface
	 */
	private function getOriginalObject() {
		return $this->originalObject;
	}

	/**
	 * Setter
	 * @param ObjectInterface $originalObject
	 */
	private function setOriginalObject(ObjectInterface $originalObject) {
		$this->originalObject = $originalObject;
	}

	/**
	 * Getter
	 * @return string
	 */
	private function getOriginalMapping() {
		return $this->originalMapping;
	}

	/**
	 * Setter
	 * @param string $originalMapping
	 */
	private function setOriginalMapping($originalMapping) {
		$this->originalMapping = $originalMapping;
	}

	/**
	 * parseInternal
	 * @param $mapping
	 * @param $until
	 * @return ObjectInterface
	 * @throws CatchableException
	 * @throws \Exception
	 */
	private function parseInternal($mapping, $until) {
		$mappingLength = strlen($mapping);
		$totalOutput = null;
		$currentMapping = '';
		$inQuote = false;
		$currentObject = $this->getOriginalObject();
		$closingParenthesisFound = false;
		for($i=0;$i<$mappingLength;$i++) {
			$char = $mapping[$i];
			$atEnd = $i+1==$mappingLength;
			$nextChar = $atEnd?null:$mapping[$i+1];
			if($char=='\\') {
				if($atEnd) {
					$this->throwMappingException('An escape character "\\" must either be escaped or not at the end of a mapping', $mapping, $i);
				}
				//Add the next character since we are escaping it
				$currentMapping .= $nextChar;
				$i++; //Skip the next character
				continue;
			}
			if($inQuote) {
				if($char=='"') {
					if(!($atEnd || $nextChar=='+' || $nextChar=='.' || ($until==self::UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS && ($nextChar==')' || $nextChar==',')))) {
						$this->throwMappingException('A closing quote "\"" must either be followed by the end of the mapping, a closing parenthesis, a plus, a "," or a .', $mapping, $i);
					}
					$inQuote = false;
					if(is_numeric($currentMapping)) {
						$currentObject = new Number($currentMapping);
					} else {
						$currentObject = new String($currentMapping);
					}
					$currentMapping = '';
					continue;
				}
				$currentMapping .= $char;
				continue;
			}
			if($char=='"') {
				if($currentMapping!='') {
					$this->throwMappingException('A beginning quote "\"" must be at the beginning of a mapping', $mapping, $i);
				}
				$inQuote = true;
				continue;
			}
			if($char=='(') {
				/** @type Parsable[] $parameters */
				$parameters = [];
				$i++; //Offset for opening parenthesis
				$subMapping = substr($mapping, $i);
				if($subMapping=='') {
					$this->throwMappingException('There are more opening parenthesis than closing parenthesis', $mapping, $i);
				}
				while(strlen($subMapping)>0) {
					$parse = new Parse($currentObject);
					$parameter = $parse->parseInternal($subMapping, self::UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS);
					$isLastParameter = substr($subMapping, $parse->getLastIndex(), 1)==')';
					if(!$isLastParameter || $parse->getLastIndex()>0) {
						$parameters[] = $parameter;
					}
					$i += $parse->getLastIndex()+1;
					$subMapping = substr($subMapping, $parse->getLastIndex()+1);
					if($isLastParameter) {
						break; //Found closing parenthesis
					}
				}
				$currentObject = $this->call($mapping, $i, $currentObject, $currentMapping, $parameters);
				$currentMapping = '';
				continue;
			}
			if($char==')') {
				if($until!=self::UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS) {
					$this->throwMappingException('There are more closing parenthesis than opening parenthesis', $mapping, $i);
				}
				$closingParenthesisFound = true;
				$this->setLastIndex($i);
				break;
			}
			if($char==',') {
				if($until!=self::UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS) {
					$this->throwMappingException('Commas are only allowed inside of parameters for methods', $mapping, $i);
				}
				$closingParenthesisFound = true;
				$this->setLastIndex($i);
				break;
			}
			if($char=='+') {
				$currentObject = $this->call($mapping, $i, $currentObject, $currentMapping);
				$this->addToTotalOutput($currentObject);
				//Reset mapping/object for next part
				$currentMapping = '';
				$currentObject = $this->getOriginalObject();
				continue;
			}
			if($char=='.') {
				$currentObject = $this->call($mapping, $i, $currentObject, $currentMapping);
				$currentMapping = '';
				continue;
			}
			$currentMapping .= $char;
		}
		if(!$closingParenthesisFound && $until==self::UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS) {
			$this->throwMappingException('There are more opening parenthesis than closing parenthesis', $mapping, $i);
		}
		if($inQuote) {
			$this->throwMappingException('There is no matching closing quote to an opening quote', $mapping, $mappingLength);
		}
		$currentObject = $this->call($mapping, $i, $currentObject, $currentMapping);
		$this->addToTotalOutput($currentObject);
		return $this->getTotalOutput();
	}

	/**
	 * call
	 * @param string          $rawMapping
	 * @param int             $index
	 * @param ObjectInterface $currentObject
	 * @param string          $currentMapping
	 * @param array           $parameters
	 * @return ObjectInterface
	 * @throws \Exception
	 */
	private function call($rawMapping, $index, ObjectInterface $currentObject, $currentMapping, array $parameters = []) {
		if($currentMapping=='') {
			return $currentObject;
		}
		$mappableFields = $currentObject->getMappableFields();
		Assert::isArray($mappableFields);
		$validMappings = [];
		foreach($mappableFields as $mappableField) {
			$validMappings[] = $mappableField->getName();
			if($mappableField->getName()==$currentMapping) {
				try {
					$returnVar = $mappableField->handle($parameters);
				} catch(CatchableException $e) {
					$this->throwMappingException($e->getMessage(), $currentMapping, $index);
				}
				if(is_object($returnVar) && $returnVar instanceof ObjectInterface) {
					return $returnVar;
				} elseif($mappableField->getReturnType()==Method::RETURN_STRING || is_null($returnVar)) {
					return new String($returnVar);
				} elseif(in_array($mappableField->getReturnType(), [Method::RETURN_DOUBLE, Method::RETURN_INT])) {
					return new Number($returnVar);
				} elseif(in_array($mappableField->getReturnType(), [Method::RETURN_DATE, Method::RETURN_DATETIME])) {
					return new Date($returnVar);
				} elseif($mappableField->getReturnType()==Method::RETURN_BOOLEAN) {
					return new Boolean($returnVar);
				} elseif($mappableField->getReturnType()==Method::RETURN_MAP) {
					return new Map($returnVar);
				} else {
					throw new \Exception('To Implement: '.$mappableField->getReturnType());
				}
			}
		}
		$this->throwMappingException('Could not find a method with the name of: '.$currentMapping.' valid options are: '.implode("\n", $validMappings), $rawMapping, $index);
	}

	/**
	 * parse
	 * @param string $mapping
	 * @return mixed
	 */
	public function parse($mapping) {
		$parser = new Parser();
		$methods = $parser->parse($mapping);
		foreach($methods as $method) {
			$currentObject = $this->getOriginalObject();
			do {
				$method->setCurrentObject($currentObject);
				$method->setOriginalObject($this->getOriginalObject());
				$method->setOriginalMapping($mapping);
				$currentObject = $method->call();
				$method = $method->getNextMethod();
			} while(!is_null($method));
			$this->addToTotalOutput($currentObject);
		}
		return $this->getTotalOutput();
	}

	/**
	 * addToTotalOutput
	 * @param ObjectInterface $currentObject
	 * @return void
	 */
	private function addToTotalOutput(ObjectInterface $currentObject=null) {
		$totalOutput = $this->getTotalOutput();
		if(is_null($totalOutput)) {
			$totalOutput = $currentObject;
		} elseif($currentObject instanceof StringInterface) {
			$totalOutput .= $currentObject;
		} else {
			$this->throwMappingException('This object: '.get_class($currentObject).' could not be converted to a string', '', 0);
		}
		$this->setTotalOutput($totalOutput);
	}

	/**
	 * throwMappingException
	 * @param string $message
	 * @param string $mapping
	 * @param int    $index
	 * @return void
	 * @throws CatchableException
	 */
	private function throwMappingException($message, $mapping, $index) {
		throw new CatchableException('Invalid Mapping: '.$message.' looking at: '.$mapping.' (Offset: '.$index.') with full mapping '.$this->getOriginalMapping());
	}

}