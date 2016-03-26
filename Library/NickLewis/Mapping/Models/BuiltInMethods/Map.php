<?php
namespace NickLewis\Mapping\Models\BuiltInMethods;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use NickLewis\Mapping\Models\BooleanInterface;
use NickLewis\Mapping\Models\MapInterface;
use NickLewis\Mapping\Models\Root;
use NickLewis\Mapping\Services\CatchableException;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;
use NickLewis\Mapping\Services\Parse;
use NickLewis\Mapping\Services\Lexicon\Method as LexiconMethod;

class Map {
    /** @type  MapInterface */
    private $model;

    /**
     * String constructor.
     * @param MapInterface $model
     */
    public function __construct(MapInterface $model) {
        $this->setModel($model);
    }

    /**
     * addMethods
     * @return Method[]
     */
    public function addMethods() {
        return [
            $this->addCount(),
            $this->addFilter(),
            $this->addItemAt()
        ];
    }

    /**
     * addItemAt
     * @return Method
     * @throws \Exception
     */
    private function addItemAt() {
        $method = new Method();
        $method->setName('itemAt');
        $method->setDescription('Gets an item from the array at the specific index');
        $method->setReturnType(Method::RETURN_MIXED);
        $method->setHandler([$this, 'mappableItemAt']);

        $parameter = new Parameter();
        $parameter->setDescription("The index in the array");
        $parameter->setAllowedType(Method::RETURN_STRING);
        $method->addParameter($parameter);

        return $method;
    }

    public function mappableItemAt($index) {
        $index = Assert::isString($index);
        if(!array_key_exists($index, $this->getModel()->getValue())) {
            throw new CatchableException('No Index could be found (available: '.implode(', ', array_keys($this->getModel()->getValue())).')');
        }
        return $this->getModel()->getValue()[$index];
    }

    /**
     * addFilter
     * @return Method
     * @throws \Exception
     */
    private function addFilter() {
        $method = new Method();
        $method->setName('filter');
        $method->setDescription('Filters an array down based off of a sub parsable string');
        $method->setReturnType(Method::RETURN_MAP);
        $method->setHandler([$this, 'mappableFilter']);

        $parameter = new Parameter();
        $parameter->setDescription('The sub parsable string.');
        $parameter->setAsParsable(true);
        $parameter->setAllowedType(Method::RETURN_STRING);
        $method->addParameter($parameter);
        return $method;
    }

    /**
     * mappableFilter
     * @param LexiconMethod[] $subParsed
     * @return array
     */
    public function mappableFilter(array $subParsed) {
        $outputValue = [];
        foreach($this->getModel()->getValue() as $key=>$value) {
            $output = Parameter::parseLexiconParameter($subParsed, false, Root::createObject($value));
            if($output instanceof BooleanInterface) {
                /** @var BooleanInterface $output */
                $output = $output->getValue();
            }
            if($output) {
                $outputValue[] = $value;
            }
        }
        return $outputValue;
    }

    /**
     * addNot
     * @return Method
     * @throws \Exception
     */
    private function addCount() {
        $method = new Method();
        $method->setName('count');
        $method->setDescription('Gets the count of an array');
        $method->setReturnType(Method::RETURN_INT);
        $method->setHandler([$this, 'mappableCount']);

        return $method;
    }

    /**
     * mappableCount
     * @return int
     */
    public function mappableCount() {
        return count($this->getModel()->getValue());
    }

    /**
     * @return MapInterface
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @param MapInterface $model
     * @return String
     */
    public function setModel(MapInterface $model) {
        $this->model = $model;
        return $this;
    }

}