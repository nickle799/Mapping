<?php
namespace NickLewis\Mapping\Models\BuiltInMethods;
use Bullhorn\FastRest\Api\Services\DataValidation\Assert;
use NickLewis\Mapping\Models\BooleanInterface;
use NickLewis\Mapping\Models\MapInterface;
use NickLewis\Mapping\Services\Method;
use NickLewis\Mapping\Services\Parameter;
use NickLewis\Mapping\Services\Parse;

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
            $this->addFilter()
        ];
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
        $parameter->setDescription('The sub parsable string. Quotes must be escaped');
        $parameter->setAllowedType(Method::RETURN_STRING);
        $method->addParameter($parameter);
        return $method;
    }

    public function mappableFilter($subParsableString) {
        $subParsableString = Assert::isString($subParsableString);
        $outputValue = [];
        foreach($this->getModel()->getValue() as $key=>$value) {

            $parse = Parse::createParse($value);
            $output = $parse->parse($subParsableString);
            if($output instanceof BooleanInterface) {
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