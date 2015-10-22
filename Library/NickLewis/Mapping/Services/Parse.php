<?php
namespace NickLewis\Mapping\Services;
use NickLewis\Mapping\Models\ObjectInterface;

class Parse {
	/** @var  ObjectInterface */
	private $currentObject;
	/** @var  string */
	private $originalMapping;

	/**
	 * @param ObjectInterface $currentObject
	 */
	public function __construct(ObjectInterface $currentObject) {
		$this->setCurrentObject($currentObject);
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
	 * parse
	 *
	 * @param string $mapping
	 *
	 * @return mixed
	 * @throws CatchableException
	 */
	public function parse($mapping) {
		if(is_null($this->getOriginalMapping())) {
			$this->setOriginalMapping($mapping);
		}
		$allParts = explode('+', $mapping);
		if(sizeOf($allParts)==1) {
			return $this->parseRecursive($mapping);
		} else {
			$returnVar = '';
			foreach ($allParts as $allPart) {
				$returnVar .= $this->parseRecursive($allPart);
			}
			return $returnVar;
		}
	}

	private function parseRecursive($mapping) {
		if(substr($mapping, 0, 1)=='"' && substr($mapping, -1)=='"') {
			return substr($mapping, 1, -1);
		}
		$parts = explode('.', $mapping);
		$currentPart = array_shift($parts);

		$mappableFields = $this->getCurrentObject()->getMappableFields();
		$validMappings = [];
		foreach($mappableFields as $mappableField) {
			$validMappings[] = $mappableField->getName();
			if($mappableField->getName()==$currentPart) {
				if(sizeOf($parts)==0) { //On last part
					return $mappableField->handle();
				} else {
					if(!$mappableField->isReturnTypeMappable()) {
						throw new CatchableException('Invalid Mapping: Return Type is Not Valid: '.$currentPart.' on '.$this->getOriginalMapping());
					}
					$parse = new self($mappableField->handle());
					$parse->setOriginalMapping($this->getOriginalMapping());
					return $parse->parseRecursive(implode('.', $parts));
				}
			}
		}
		throw new CatchableException('Invalid Mapping: Could Not Find the Part: '.$currentPart.' on '.$this->getOriginalMapping().', valid mappings are:'."\n".implode("\n", $validMappings));
	}

	/**
	 * Getter
	 * @return ObjectInterface
	 */
	private function getCurrentObject() {
		return $this->currentObject;
	}

	/**
	 * Setter
	 * @param ObjectInterface $currentObject
	 */
	private function setCurrentObject(ObjectInterface $currentObject) {
		$this->currentObject = $currentObject;
	}

}