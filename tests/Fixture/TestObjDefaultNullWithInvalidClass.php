<?php

namespace Zit\Fixture;

class TestObjDefaultNullWithInvalidClass
{
    public $name = 1;
    public function __construct(\kakaw $name = null)
    {
        $this->name = $name;
    }
}