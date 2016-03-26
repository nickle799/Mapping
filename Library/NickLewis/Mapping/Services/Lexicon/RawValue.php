<?php
namespace NickLewis\Mapping\Services\Lexicon;
use NickLewis\Mapping\Models\Number as ModelNumber;
use NickLewis\Mapping\Models\String as ModelString;

class RawValue {
    /** @var  ModelNumber|ModelString */
    private $value;

    /**
     * RawValue constructor.
     * @param ModelNumber|ModelString $value
     */
    public function __construct($value) {
        $this->value = $value;
    }


    /**
     * Getter
     * @return ModelNumber|ModelString
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Setter
     * @param ModelNumber|ModelString $value
     * @return RawValue
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }


}