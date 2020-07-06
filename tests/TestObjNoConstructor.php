<?php

namespace Zit;

class TestObjNoConstructor
{
    public $name;
    public $instance;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setOptionalValue($instance = 'kakaw')
    {
        $this->instance = $instance;
    }

    public function setOptionalValueNull($instance)
    {
        $this->instance = $instance;
    }

    public function setTypedInstance(TestObjNoConstructor $instance)
    {
        $this->instance = $instance;
    }

    public function setBadClass(MadeUpClassThatShouldNotExist $instance)
    {
        // nothing to do
    }

    public function setNullTypedInstance(?TestObjNoConstructor $instance)
    {
        $this->instance = $instance;
    }

    public function setBuiltInType(string $string = null)
    {
        $this->name = $string;
    }

    public static function staticFactoy()
    {
        return "static data";
    }
}