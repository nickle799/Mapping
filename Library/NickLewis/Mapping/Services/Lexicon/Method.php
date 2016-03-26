<?php
namespace NickLewis\Mapping\Services\Lexicon;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use NickLewis\Mapping\Models\Boolean;
use NickLewis\Mapping\Models\Date;
use NickLewis\Mapping\Models\Map;
use NickLewis\Mapping\Models\Number;
use NickLewis\Mapping\Models\ObjectInterface;
use NickLewis\Mapping\Models\Root;
use NickLewis\Mapping\Models\String;
use NickLewis\Mapping\Services\CatchableException;
use NickLewis\Mapping\Services\Method as ParseMethod;

class Method {
    /** @var  string|RawValue */
    private $name;
    /** @var  null|Method */
    private $nextMethod;
    /** @var  Method[] */
    private $parameters = [];
    /** @var  string */
    private $rawMapping;
    /** @var  int */
    private $rawMappingIndex;
    /** @var  string */
    private $originalMapping;
    /** @var  ObjectInterface */
    private $currentObject;
    /** @var  ObjectInterface */
    private $originalObject;

    /**
     * Method constructor.
     * @param string|RawValue $name
     */
    public function __construct($name) {
        $this->setName($name);
    }

    /**
     * Getter
     * @return ObjectInterface
     */
    public function getOriginalObject() {
        return $this->originalObject;
    }

    /**
     * Setter
     * @param ObjectInterface $originalObject
     * @return Method
     */
    public function setOriginalObject(ObjectInterface $originalObject) {
        $this->originalObject = $originalObject;
        return $this;
    }

    /**
     * Getter
     * @return ObjectInterface
     */
    public function getCurrentObject() {
        return $this->currentObject;
    }

    /**
     * Setter
     * @param ObjectInterface $currentObject
     * @return Method
     */
    public function setCurrentObject(ObjectInterface $currentObject) {
        $this->currentObject = $currentObject;
        return $this;
    }

    /**
     * Getter
     * @return string
     */
    public function getOriginalMapping() {
        return $this->originalMapping;
    }

    /**
     * Setter
     * @param string $originalMapping
     * @return Method
     */
    public function setOriginalMapping($originalMapping) {
        $this->originalMapping = $originalMapping;
        return $this;
    }

    /**
     * Getter
     * @return int
     */
    public function getRawMappingIndex() {
        return $this->rawMappingIndex;
    }

    /**
     * Setter
     * @param int $rawMappingIndex
     * @return Method
     */
    public function setRawMappingIndex($rawMappingIndex) {
        $this->rawMappingIndex = $rawMappingIndex;
        return $this;
    }

    /**
     * Getter
     * @return string
     */
    public function getRawMapping() {
        return $this->rawMapping;
    }

    /**
     * Setter
     * @param string $rawMapping
     * @return Method
     */
    public function setRawMapping($rawMapping) {
        $this->rawMapping = $rawMapping;
        return $this;
    }



    /**
     * Getter
     * @return string|RawValue
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Setter
     * @param string|RawValue $name
     * @return Method
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Getter
     * @return Method|null
     */
    public function getNextMethod() {
        return $this->nextMethod;
    }

    /**
     * Setter
     * @param Method|null $nextMethod
     * @return Method
     */
    public function setNextMethod(Method $nextMethod) {
        $this->nextMethod = $nextMethod;
        return $this;
    }

    /**
     * Getter
     * @return Method[]
     */
    public function getParameters() {
        foreach($this->parameters as $parameter) {
            foreach($parameter as $subParameter) {
                $subParameter->setOriginalObject($this->getOriginalObject());
                $subParameter->setCurrentObject($this->getCurrentObject());
            }
        }
        return $this->parameters;
    }

    /**
     * Setter
     * @param Method[] $parameters
     * @return Method
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * call
     * @param bool|false $useOriginalObject
     * @return Boolean|Map|Number|ObjectInterface|String
     * @throws CatchableException
     * @throws \Exception
     */
    public function call($useOriginalObject=false) {
        if($this->getName() instanceof RawValue) {
            return $this->getName()->getValue();
        } else {
            $currentObject = $useOriginalObject?$this->getOriginalObject():$this->getCurrentObject();
            if($this->getName()=='') {
                return $currentObject;
            }
            $mappableFields = $currentObject->getMappableFields();
            Assert::isArray($mappableFields);
            $validMappings = [];
            foreach($mappableFields as $mappableField) {
                $validMappings[] = $mappableField->getName();
                if($mappableField->getName()==$this->getName()) {
                    try {
                        $returnVar = $mappableField->handle($this->getParameters());
                    } catch(CatchableException $e) {
                        $this->throwMappingException($e->getMessage());
                    }
                    if(is_object($returnVar) && $returnVar instanceof ObjectInterface) {
                        return $returnVar;
                    } elseif($mappableField->getReturnType()==ParseMethod::RETURN_STRING || is_null($returnVar)) {
                        return new String($returnVar);
                    } elseif(in_array($mappableField->getReturnType(), [ParseMethod::RETURN_DOUBLE, ParseMethod::RETURN_INT])) {
                        return new Number($returnVar);
                    } elseif(in_array($mappableField->getReturnType(), [ParseMethod::RETURN_DATE, ParseMethod::RETURN_DATETIME])) {
                        return new Date($returnVar);
                    } elseif($mappableField->getReturnType()==ParseMethod::RETURN_BOOLEAN) {
                        return new Boolean($returnVar);
                    } elseif($mappableField->getReturnType()==ParseMethod::RETURN_MAP) {
                        return new Map($returnVar);
                    } elseif($mappableField->getReturnType()==ParseMethod::RETURN_MIXED) {
                        return Root::createObject($returnVar);
                    } else {
                        throw new \Exception('To Implement: '.$mappableField->getReturnType());
                    }
                }
            }
            $this->throwMappingException('Could not find a method with the name of: '.$this->getName().' valid options are: '.implode("\n", $validMappings));
        }
    }

    /**
     * throwMappingException
     * @param string $message
     * @param string $mapping
     * @param int    $index
     * @return void
     * @throws CatchableException
     */
    private function throwMappingException($message) {
        throw new CatchableException('Invalid Mapping: '.$message.' looking at: '. $this->getName().' (Offset: '.$this->getRawMappingIndex().') with full mapping '.$this->getOriginalMapping());
    }


}