<?php

namespace Zit\Fixture;

class TestObj
{
    public $name;
    public $instance;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}