<?php
namespace NickLewis\Mapping\Services\Lexicon;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use NickLewis\Mapping\Models\Number;
use NickLewis\Mapping\Models\String;
use NickLewis\Mapping\Services\CatchableException;

class Parser {
    const UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS = 2;
    const UNTIL_END = 1;
    /** @type  string */
    private $originalMapping;
    /** @type int  */
    private $lastIndex = 0;
    /** @var null|Method */
    private $currentStartingMethod = null;
    /** @var null|Method */
    private $currentMethod = null;
    /** @var Method[] */
    private $methods = [];

    public function parse($mapping) {
        $mapping = Assert::isString($mapping);
        $this->setOriginalMapping($mapping);
        $this->parseInternal($mapping, self::UNTIL_END);
        return $this->getMethods();
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
     * @return Parser
     */
    private function setOriginalMapping($originalMapping) {
        $this->originalMapping = $originalMapping;
        return $this;
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
     * @return Method|null
     */
    private function getCurrentStartingMethod() {
        return $this->currentStartingMethod;
    }

    /**
     * Setter
     * @param Method|null $currentStartingMethod
     * @return Parser
     */
    private function setCurrentStartingMethod($currentStartingMethod) {
        $this->currentStartingMethod = $currentStartingMethod;
        return $this;
    }

    /**
     * Getter
     * @return Method|null
     */
    private function getCurrentMethod() {
        return $this->currentMethod;
    }

    /**
     * Setter
     * @param Method|null $currentMethod
     * @return Parser
     */
    private function setCurrentMethod($currentMethod) {
        $this->currentMethod = $currentMethod;
        return $this;
    }

    /**
     * Getter
     * @return Method[]
     */
    private function getMethods() {
        return $this->methods;
    }

    /**
     * Setter
     * @param Method[] $methods
     * @return Parser
     */
    private function setMethods(array $methods) {
        $this->methods = $methods;
        return $this;
    }




    /**
     * addToCurrentMethod
     * @param Method $method
     * @return void
     */
    private function addToCurrentMethod(Method $method) {
        if(is_null($this->getCurrentMethod())) {
            $this->setCurrentStartingMethod($method);
        } else {
            $this->getCurrentMethod()->setNextMethod($method);
        }
        $this->setCurrentMethod($method);
    }

    private function finishCurrentMethod() {
        if(!is_null($this->getCurrentStartingMethod())) {
            $methods = $this->getMethods();
            $methods[] = $this->getCurrentStartingMethod();
            $this->setMethods($methods);
        }
        $this->setCurrentStartingMethod(null);
        $this->setCurrentMethod(null);
    }

    /**
     * parseInternal
     * @param $mapping
     * @param $until
     * @return void
     * @throws CatchableException
     * @throws \Exception
     */
    private function parseInternal($mapping, $until) {
        $mappingLength = strlen($mapping);
        $currentMapping = '';
        $inQuote = false;
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
                        $object = new Number($currentMapping);
                    } else {
                        $object = new String($currentMapping);
                    }
                    $this->addToCurrentMethod(new Method(new RawValue($object)));
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
                /** @type Method[] $parameters */
                $parameters = [];
                $i++; //Offset for opening parenthesis
                $subMapping = substr($mapping, $i);
                if($subMapping=='') {
                    $this->throwMappingException('There are more opening parenthesis than closing parenthesis', $mapping, $i);
                }
                while(strlen($subMapping)>0) {
                    $parse = new Parser();
                    $parse->setOriginalMapping($this->getOriginalMapping());
                    $parse->parseInternal($subMapping, self::UNTIL_CLOSING_COMMA_OR_CLOSING_PARENTHESIS);
                    $parameter = $parse->getMethods();
                    $isLastParameter = substr($subMapping, $parse->getLastIndex(), 1)==')';
                    if(!$isLastParameter || $parse->getLastIndex()>0) {
                        $parameters[] = $parameter;
                    }
                    $i += $parse->getLastIndex()+1;
                    $subMapping = substr($subMapping, $parse->getLastIndex()+1);
                    if($isLastParameter) {
                        $i-=1;
                        break; //Found closing parenthesis
                    }
                }
                $method = new Method($currentMapping);
                $method->setParameters($parameters);
                $method->setRawMapping($mapping);
                $method->setRawMappingIndex($i);
                $this->addToCurrentMethod($method);
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
                $method = new Method($currentMapping);
                $method->setRawMapping($mapping);
                $method->setRawMappingIndex($i);
                $this->addToCurrentMethod($method);
                $this->finishCurrentMethod();

                //Reset mapping/object for next part
                $currentMapping = '';
                continue;
            }
            if($char=='.') {
                $method = new Method($currentMapping);
                $method->setRawMapping($mapping);
                $method->setRawMappingIndex($i);
                $this->addToCurrentMethod($method);
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
        $method = new Method($currentMapping);
        $method->setRawMapping($mapping);
        $method->setRawMappingIndex($i);
        $this->addToCurrentMethod($method);
        $this->finishCurrentMethod();
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