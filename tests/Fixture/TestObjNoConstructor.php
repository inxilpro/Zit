<?php

namespace Zit\Fixture;

class TestObjNoConstructor
{
    public $name;
    public $instance;
    public $missing = true;
    public $container;

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

    public function setInvalidType(callable $instance)
    {
        //nothing to do
    }

    public function setNullTypedInstance(?TestObjNoConstructor $instance)
    {
        $this->instance = $instance;
    }

    public function setBuiltInType(string $string = null)
    {
        $this->name = $string;
    }

    public function setMissing($value)
    {
        $this->missing = $value;
    }

    public function setMissingContainer($container)
    {
        $this->container = $container;
    }

    public static function staticFactoy()
    {
        return "static data";
    }
}