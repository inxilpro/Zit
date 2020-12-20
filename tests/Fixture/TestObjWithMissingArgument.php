<?php

namespace Zit\Fixture;

class TestObjWithMissingArgument
{
    public $name;
    public $instance;

    public function __construct($name= null)
    {
        $this->name = $name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}