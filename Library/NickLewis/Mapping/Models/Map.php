<?php
namespace NickLewis\Mapping\Models;
class Map extends Root implements MapInterface {
    /** @type  array */
    private $value;

    /**
     * String constructor.
     * @param array $value
     */
    public function __construct(array $value) {
        $this->setValue($value);
    }


    /**
     * Getter
     * @return array
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Setter
     * @param array $value
     */
    private function setValue(array $value) {
        $this->value = $value;
    }
}